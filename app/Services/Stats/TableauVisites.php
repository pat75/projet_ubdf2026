<?php

namespace App\Services\Stats;

use App\Models\User;

/**
 * Visites du book d'un creatif : 90 jours par surface, 12 derniers mois,
 * total. Partage par la page Statistiques de l'espace et l'API.
 */
class TableauVisites
{
    public const JOURS = 90;

    /**
     * @return array{par_jour: array<string, array<string, int>>, par_mois: array<string, int>, total: int}
     *                         par_jour indexe par date (Y-m-d), par_mois par mois (Y-m)
     */
    public function pour(User $creatif): array
    {
        $stats = $creatif->visitStats();

        // Plusieurs lignes par jour (une par surface) : on les regroupe.
        $lignes = $stats->clone()
            ->where('date', '>=', now()->subDays(self::JOURS - 1)->toDateString())
            ->selectRaw('date, surface, SUM(public_views) as vues')
            ->groupBy('date', 'surface')
            ->get()
            ->groupBy(fn ($l) => $l->date->toDateString());

        $parJour = collect(range(self::JOURS - 1, 0))->mapWithKeys(function ($i) use ($lignes) {
            $date = now()->subDays($i)->toDateString();
            $surfaces = collect(CompteurVisites::SURFACES)->mapWithKeys(fn ($s) => [$s => 0]);

            foreach ($lignes[$date] ?? [] as $ligne) {
                $surfaces[$ligne->surface] = (int) $ligne->vues;
            }

            return [$date => $surfaces->all()];
        });

        $mois = $stats->clone()
            ->where('date', '>=', now()->startOfMonth()->subMonths(11)->toDateString())
            ->get(['date', 'public_views'])
            ->groupBy(fn ($s) => $s->date->format('Y-m'))
            ->map->sum('public_views');

        $parMois = collect(range(11, 0))->mapWithKeys(function ($i) use ($mois) {
            $cle = now()->startOfMonth()->subMonths($i)->format('Y-m');

            return [$cle => (int) ($mois[$cle] ?? 0)];
        });

        return [
            'par_jour' => $parJour->all(),
            'par_mois' => $parMois->all(),
            // Comme la carte du portail (User::vuesTotales) : vues du serveur
            // de stats legacy (extra-book), plus celles comptees depuis.
            'total' => (int) $creatif->legacy_views + (int) $stats->clone()->sum('public_views'),
        ];
    }
}
