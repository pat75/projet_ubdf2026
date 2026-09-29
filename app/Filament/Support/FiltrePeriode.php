<?php

namespace App\Filament\Support;

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * « Depuis quand ? » sur une colonne de date.
 *
 * Trois filtres de ce genre reviennent sur chaque liste — inscription,
 * mise en selection, dernier achat de formule — avec les memes bornes.
 * Un intervalle de dates libre (deux calendriers) serait plus general,
 * mais dans la pratique on demande toujours « ce mois-ci » ou « cette
 * annee » : une liste fermee se pose en un clic la ou deux calendriers
 * en demandent quatre.
 */
class FiltrePeriode
{
    /** Bornes proposees, en jours. */
    private const BORNES = [
        '7' => 'Les 7 derniers jours',
        '30' => 'Les 30 derniers jours',
        '90' => 'Les 3 derniers mois',
        '365' => 'Les 12 derniers mois',
    ];

    /**
     * @param  string  $colonne  Colonne de date filtree (`created_at`…).
     * @param  bool  $jamais  Ajoute « Jamais » : n'a de sens que pour une
     *                        date facultative (une selection, un achat),
     *                        pas pour une date d'inscription.
     */
    public static function make(string $nom, string $libelle, string $colonne, bool $jamais = false): SelectFilter
    {
        return SelectFilter::make($nom)
            ->label($libelle)
            ->options(self::BORNES + ($jamais ? ['jamais' => 'Jamais'] : []))
            // Le parametre doit s'appeler $query et $data : Filament injecte
            // par le nom, un autre nom recevrait un constructeur vide.
            ->query(function (Builder $query, array $data) use ($colonne): Builder {
                $choix = $data['value'] ?? null;

                if (blank($choix)) {
                    return $query;
                }

                return $choix === 'jamais'
                    ? $query->whereNull($colonne)
                    : $query->where($colonne, '>=', now()->subDays((int) $choix));
            });
    }
}
