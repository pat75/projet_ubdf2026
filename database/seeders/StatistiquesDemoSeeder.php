<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Visitor;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Jeu d'essai des courbes du tableau de bord, en attendant l'import
 * complet de `ub2020` : la base de travail n'a que huit comptes, et
 * quatre graphiques vides ne se relisent pas.
 *
 * A lancer a la main, jamais depuis `DatabaseSeeder` :
 *
 *     php artisan db:seed --class=StatistiquesDemoSeeder
 *
 * Tout ce qu'il cree porte le prefixe `demo-` (login des creatifs,
 * adresses, numeros de facture) : chaque passage retire d'abord le jeu
 * precedent, et le jour de l'import il suffira de le purger une derniere
 * fois.
 */
class StatistiquesDemoSeeder extends Seeder
{
    private const PREFIXE = 'demo-';

    public function run(): void
    {
        $this->purger();

        $cetteAnnee = (int) CarbonImmutable::now()->year;

        foreach ([$cetteAnnee - 1, $cetteAnnee] as $annee) {
            // L'annee en cours s'arrete aujourd'hui : des inscriptions
            // datees de decembre prochain fausseraient la comparaison.
            $fin = $annee === $cetteAnnee
                ? CarbonImmutable::now()
                : CarbonImmutable::create($annee, 12, 31);

            // Un peu plus de monde cette annee que l'an dernier : l'ecart
            // doit se voir sur le graphique, sinon on ne verifie rien.
            $creatifs = $annee === $cetteAnnee ? 70 : 55;

            $this->creatifs($annee, $fin, $creatifs);
            $this->visiteurs($annee, $fin, $annee === $cetteAnnee ? 45 : 30);
        }

        Cache::flush();

        $this->command?->info('Jeu d’essai en place (comptes et factures « demo- »).');
    }

    private function creatifs(int $annee, CarbonImmutable $fin, int $combien): void
    {
        for ($i = 0; $i < $combien; $i++) {
            $inscrit = $this->dateAuHasard($annee, $fin);

            $creatif = User::factory()->create([
                'login' => self::PREFIXE.Str::lower(Str::random(10)),
                'email' => self::PREFIXE.Str::lower(Str::random(10)).'@exemple.test',
                'created_at' => $inscrit,
                'updated_at' => $inscrit,
            ]);

            // Un compte sur six repart : de quoi peupler la courbe des
            // desinscriptions sans vider les deux autres.
            if ($i % 6 === 0) {
                $creatif->deleted_at = $this->entre($inscrit, $fin);
                $creatif->saveQuietly();
            }

            // Une facture sur deux : la moitie des inscrits prend une
            // formule payante, l'autre reste en gratuit. Elle est datee
            // du mois de l'inscription, comme dans la vraie vie — etalee
            // jusqu'a fin decembre, elle tasserait tout le chiffre
            // d'affaires sur la fin de l'annee precedente.
            if ($i % 2 === 0) {
                $this->facture($creatif, $this->entre($inscrit, min($inscrit->addDays(30), $fin)));
            }
        }
    }

    private function visiteurs(int $annee, CarbonImmutable $fin, int $combien): void
    {
        for ($i = 0; $i < $combien; $i++) {
            $inscrit = $this->dateAuHasard($annee, $fin);

            $visiteur = Visitor::factory()->create([
                'email' => self::PREFIXE.Str::lower(Str::random(10)).'@exemple.test',
                'created_at' => $inscrit,
                'updated_at' => $inscrit,
            ]);

            if ($i % 8 === 0) {
                $visiteur->deleted_at = $this->entre($inscrit, $fin);
                $visiteur->saveQuietly();
            }
        }
    }

    private function facture(User $creatif, CarbonImmutable $date): void
    {
        $montant = [29.80, 32.80, 36.80, 59.60][random_int(0, 3)];

        Invoice::create([
            'user_id' => $creatif->id,
            'brand' => 'ub',
            'number' => self::PREFIXE.Str::lower(Str::random(12)),
            'designation' => 'Formule Ultra-book 12 mois',
            'amount' => $montant,
            'vat' => round($montant - $montant / 1.2, 2),
            'currency' => 'EUR',
            'status' => 'paid',
            'issued_at' => $date,
            'paid_at' => $date,
        ]);
    }

    private function dateAuHasard(int $annee, CarbonImmutable $fin): CarbonImmutable
    {
        return $this->entre(CarbonImmutable::create($annee, 1, 1), $fin);
    }

    private function entre(CarbonImmutable $debut, CarbonImmutable $fin): CarbonImmutable
    {
        $jours = max(0, $debut->diffInDays($fin));

        return $debut->addDays(random_int(0, (int) $jours))->setTime(random_int(8, 22), random_int(0, 59));
    }

    /** Retire tout ce qu'un passage precedent avait cree. */
    private function purger(): void
    {
        Invoice::where('number', 'like', self::PREFIXE.'%')->delete();
        User::withTrashed()->where('login', 'like', self::PREFIXE.'%')->forceDelete();
        Visitor::withTrashed()->where('email', 'like', self::PREFIXE.'%')->forceDelete();
    }
}
