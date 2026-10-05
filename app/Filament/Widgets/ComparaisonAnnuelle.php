<?php

namespace App\Filament\Widgets;

use App\Services\Admin\StatistiquesAnnuelles;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trois annees sur un meme graphique : l'annee en cours en bleu plein,
 * les deux precedentes en pointille gris puis gris clair, toutes cumulees
 * depuis le 1er janvier.
 *
 * La courbe de l'annee en cours s'arrete au jour present ; la description
 * du graphique donne l'ecart avec l'annee precedente arretee a la meme
 * date, seule comparaison honnete en cours d'annee.
 *
 * Une classe fille declare ce qu'elle mesure (`sources()`), son titre et,
 * si la valeur est un montant, son unite.
 */
abstract class ComparaisonAnnuelle extends ChartWidget
{
    protected ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = 1;

    /** @var array<string, mixed>|null */
    private ?array $comparaison = null;

    /** Cle de cache et d'identification de la serie. */
    abstract protected function cle(): string;

    /**
     * Sources additionnees dans la courbe : `[requete, colonne de date,
     * colonne a sommer ou null]`.
     *
     * @return array<int, array{0: Builder, 1: string, 2: string|null}>
     */
    abstract protected function sources(): array;

    /** Montant : le total de la description se lit alors « 1 234,56 € ». */
    protected function estMontant(): bool
    {
        return false;
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getDescription(): string|Htmlable|null
    {
        $c = $this->comparaison();

        $total = $this->formater($c['total']).' '.__('depuis le 1er janvier');

        if ($c['ecart'] === null) {
            return $total;
        }

        $signe = $c['ecart'] >= 0 ? '+' : '−';

        return $total.' — '.$signe.number_format(abs($c['ecart']), 1, ',', ' ').' % '
            .__('sur la même période de :annee', ['annee' => $c['annee_precedente']]);
    }

    protected function getData(): array
    {
        $c = $this->comparaison();
        $stats = app(StatistiquesAnnuelles::class);

        return [
            'datasets' => [
                [
                    'label' => (string) $c['annee'],
                    'data' => $c['serie'],
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, .08)',
                    'fill' => true,
                    'tension' => .3,
                    'pointRadius' => 0,
                    'borderWidth' => 2,
                    'spanGaps' => false,
                ],
                [
                    'label' => (string) $c['annee_precedente'],
                    'data' => $c['serie_precedente'],
                    'borderColor' => '#9ca3af',
                    'borderDash' => [4, 4],
                    'fill' => false,
                    'tension' => .3,
                    'pointRadius' => 0,
                    'borderWidth' => 2,
                ],
                [
                    'label' => (string) $c['annee_anterieure'],
                    'data' => $c['serie_anterieure'],
                    'borderColor' => '#d1d5db',
                    'borderDash' => [4, 4],
                    'fill' => false,
                    'tension' => .3,
                    'pointRadius' => 0,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $stats->libelles($c['annee']),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'bottom',
                    // padding : ecart entre les legendes des annees (~30px).
                    'labels' => ['boxWidth' => 12, 'usePointStyle' => true, 'padding' => 30]],
            ],
            'scales' => [
                // 53 dates ne tiennent pas sur la largeur d'une demi-page :
                // une etiquette par mois environ suffit a se reperer.
                'x' => ['ticks' => ['maxTicksLimit' => 12, 'autoSkip' => true,
                    'maxRotation' => 0, 'font' => ['size' => 10]]],
                'y' => ['beginAtZero' => true, 'ticks' => ['font' => ['size' => 10]]],
            ],
            'interaction' => ['mode' => 'index', 'intersect' => false],
        ];
    }

    /** @return array<string, mixed> */
    protected function comparaison(): array
    {
        return $this->comparaison ??= app(StatistiquesAnnuelles::class)
            ->comparaison($this->sources(), $this->cle());
    }

    private function formater(float $valeur): string
    {
        return $this->estMontant()
            ? number_format($valeur, 2, ',', ' ').' €'
            : number_format($valeur, 0, ',', ' ');
    }
}
