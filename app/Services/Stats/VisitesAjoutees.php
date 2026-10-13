<?php

namespace App\Services\Stats;

use App\Models\User;
use App\Models\VisiteAjoutee;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Visites ajoutees aux books peu vus (moins de PLAFOND vues).
 *
 * Un lot tire des books au hasard et planifie, pour chacun, des visites
 * jour par jour a une heure aleatoire : souvent 1 ou 2, parfois 0, au plus
 * 4. Chaque book recoit un poids propre, qui baisse a l'approche du
 * plafond, pour que deux books du lot ne suivent pas la meme courbe.
 * ubdf:visites:appliquer verse ensuite les visites echues dans
 * visit_stats : elles comptent partout comme des vues publiques.
 */
class VisitesAjoutees
{
    public const PLAFOND = 1000;

    public const MAX_PAR_JOUR = 4;

    /** Tirage d'un jour non nul : 1 et 2 dominent. */
    private const TIRAGE = [1 => 45, 2 => 35, 3 => 13, 4 => 7];

    /** Books diffuses, formule en cours, sous le plafond, hors lot en cours. */
    public function eligibles(): Builder
    {
        return User::query()
            ->where('plan', '>', 0)
            ->where('plan_expires_at', '>', now())
            ->whereHas('bookSetting', fn (Builder $query) => $query->where('diffuse_web', true)->where('diffuse_ub', true))
            ->whereDoesntHave('visitesAjoutees', fn (Builder $query) => $query->whereNull('appliquee_at'))
            ->whereRaw('users.legacy_views + (select coalesce(sum(public_views), 0) from visit_stats where visit_stats.user_id = users.id) < ?', [self::PLAFOND]);
    }

    /** Cree un lot ; rend le nombre de books retenus. */
    public function creerLot(int $nbBooks = 1000, int $jours = 30): int
    {
        // Tirage au hasard parmi les 10 000 derniers portfolios eligibles.
        $ids = $this->eligibles()->orderByDesc('id')->limit(10000)->pluck('id');
        $books = User::whereIn('id', $ids->shuffle()->take($nbBooks))->withSum('visitStats', 'public_views')->get();
        $lot = (string) Str::ulid();
        $lignes = [];

        foreach ($books as $book) {
            foreach ($this->planifier($book->vuesTotales(), $jours, now()) as $prevueAt => $nb) {
                $lignes[] = ['lot' => $lot, 'user_id' => $book->id, 'prevue_at' => $prevueAt, 'nb' => $nb,
                    'created_at' => now(), 'updated_at' => now()];
            }
        }

        // Par paquets : 1000 books x 30 jours depasse la limite de parametres MySQL.
        foreach (array_chunk($lignes, 250) as $paquet) {
            VisiteAjoutee::insert($paquet);
        }

        return $books->count();
    }

    /**
     * Visites d'un book jour par jour, cle = date-heure prevue. Les jours a
     * 0 sont omis ; le total ne fait jamais atteindre le plafond.
     *
     * @return array<string, int>
     */
    public function planifier(int $vuesActuelles, int $jours, CarbonInterface $debut): array
    {
        $reste = self::PLAFOND - 1 - $vuesActuelles;
        $poids = (mt_rand(30, 100) / 100) * max(0.2, 1 - $vuesActuelles / self::PLAFOND);
        $plan = [];

        for ($j = 0; $j < $jours && $reste > 0; $j++) {
            // Jours creux, plus frequents pour un book de faible poids.
            if (mt_rand(1, 100) <= 25 + (int) ((1 - $poids) * 35)) {
                continue;
            }

            $nb = $this->tirer();
            if ($poids < 0.5) {
                $nb = min($nb, 2);
            }
            $nb = min($nb, self::MAX_PAR_JOUR, $reste);
            $reste -= $nb;

            $heure = $debut->copy()->addDays($j)->startOfDay()->addMinutes(mt_rand(7 * 60, 23 * 60 + 59));
            $plan[$heure->toDateTimeString()] = $nb;
        }

        return $plan;
    }

    /** Verse dans visit_stats les visites dont l'heure est passee. */
    public function appliquer(): int
    {
        $echues = VisiteAjoutee::whereNull('appliquee_at')->where('prevue_at', '<=', now())->get();

        foreach ($echues as $visite) {
            DB::transaction(function () use ($visite) {
                DB::table('visit_stats')->upsert(
                    [['user_id' => $visite->user_id, 'date' => $visite->prevue_at->toDateString(), 'surface' => 'book',
                        'public_views' => $visite->nb, 'created_at' => now(), 'updated_at' => now()]],
                    ['user_id', 'date', 'surface'],
                    ['public_views' => DB::raw('public_views + '.(int) $visite->nb), 'updated_at' => now()],
                );
                $visite->update(['appliquee_at' => now()]);
            });
        }

        return $echues->count();
    }

    /** Retire d'un book toutes ses visites ajoutees, versees ou non. */
    public function retirer(User $book): void
    {
        DB::transaction(function () use ($book) {
            foreach ($book->visitesAjoutees()->whereNotNull('appliquee_at')->get() as $visite) {
                DB::table('visit_stats')
                    ->where(['user_id' => $book->id, 'date' => $visite->prevue_at->toDateString(), 'surface' => 'book'])
                    ->where('public_views', '>=', $visite->nb)
                    ->decrement('public_views', $visite->nb);
            }
            $book->visitesAjoutees()->delete();
        });
    }

    private function tirer(): int
    {
        $n = mt_rand(1, array_sum(self::TIRAGE));
        foreach (self::TIRAGE as $nb => $chances) {
            if (($n -= $chances) <= 0) {
                return $nb;
            }
        }

        return 1;
    }
}
