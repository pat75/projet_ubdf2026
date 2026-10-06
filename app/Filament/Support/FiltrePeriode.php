<?php

namespace App\Filament\Support;

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
        '7' => '7 jours',
        '30' => '30 jours',
        '90' => '3 mois',
        '365' => '12 mois',
    ];

    /**
     * @param  string  $colonne  Colonne de date filtree (`created_at`…).
     * @param  bool  $jamais  Ajoute « Jamais » : n'a de sens que pour une
     *                        date facultative (une selection, un achat),
     *                        pas pour une date d'inscription.
     */
    public static function make(string $nom, string $libelle, string $colonne, bool $jamais = false): \Filament\Tables\Filters\Filter
    {
        $choix = [];

        foreach (self::BORNES as $jours => $texte) {
            $choix[$jours] = [$texte, fn (Builder $query) => $query->where($colonne, '>=', now()->subDays((int) $jours))];
        }

        if ($jamais) {
            $choix['jamais'] = ['Jamais', fn (Builder $query) => $query->whereNull($colonne)];
        }

        // Boutons directs plutot qu'une liste deroulante (FiltreBoutons).
        return FiltreBoutons::make($nom, $libelle, $choix);
    }
}
