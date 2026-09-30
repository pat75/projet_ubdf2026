<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\CarbonImmutable;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

/**
 * Les encaissements du jour, de la semaine, du mois et de l'annee, en
 * face du graphique du chiffre d'affaires.
 *
 * Quatre lectures du meme chiffre, de la plus courte a la plus longue :
 * le graphique donne la forme de l'annee, cette colonne dit ou l'on en
 * est aujourd'hui. Chaque ligne rappelle la meme periode de l'an
 * dernier, qui est le seul point de comparaison qui ait un sens.
 *
 * Une seule carte, et non quatre cartes empilees
 * (`StatsOverviewWidget`) : c'est ce qui lui permet d'occuper exactement
 * la hauteur du graphique d'en face, les quatre lignes se repartissant
 * l'espace disponible.
 *
 * Montants TTC (`invoices.amount`), factures payees seulement.
 */
class Encaissements extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.encaissements';

    /**
     * @return array<int, array{libelle: string, montant: string, precedent: string, annee: int, en_hausse: bool|null, variation: string|null}>
     */
    public function lignes(): array
    {
        $maintenant = CarbonImmutable::now();

        return [
            $this->ligne(__('Aujourd’hui'), $maintenant->startOfDay(), $maintenant),
            $this->ligne(__('7 derniers jours'), $maintenant->subDays(7), $maintenant),
            $this->ligne(__('Ce mois-ci'), $maintenant->startOfMonth(), $maintenant),
            $this->ligne(__('Depuis le 1er janvier'), $maintenant->startOfYear(), $maintenant),
        ];
    }

    /**
     * @return array{libelle: string, montant: string, precedent: string, annee: int, en_hausse: bool|null, variation: string|null}
     */
    private function ligne(string $libelle, CarbonImmutable $debut, CarbonImmutable $fin): array
    {
        $montant = $this->somme($debut, $fin);
        // Meme tranche, un an plus tot : « 1 200 € » ne dit rien seul.
        $precedent = $this->somme($debut->subYear(), $fin->subYear());

        // Sans encaissement l'an dernier, ni sens de variation ni
        // pourcentage : « +100 % » par rapport a zero ne veut rien dire.
        $comparable = $precedent > 0.0;

        return [
            'libelle' => $libelle,
            'montant' => $this->euros($montant),
            'precedent' => $this->euros($precedent),
            'annee' => $fin->year - 1,
            'en_hausse' => $comparable ? $montant >= $precedent : null,
            'variation' => $comparable
                ? number_format(abs(($montant - $precedent) / $precedent * 100), 1, ',', ' ').' %'
                : null,
        ];
    }

    private function somme(CarbonImmutable $debut, CarbonImmutable $fin): float
    {
        // Cle a la journee : a la minute pres, elle changerait a chaque
        // affichage et le cache ne servirait jamais.
        $cle = 'encaissements.'.$debut->format('Ymd').'.'.$fin->format('Ymd');

        return Cache::remember($cle, 600, fn () => (float) Invoice::where('status', 'paid')
            ->whereBetween('issued_at', [$debut, $fin])
            ->sum('amount'));
    }

    private function euros(float $montant): string
    {
        return number_format($montant, 2, ',', ' ').' €';
    }
}
