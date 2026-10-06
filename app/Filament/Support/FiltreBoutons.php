<?php

namespace App\Filament\Support;

use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filtre a choix directs : les valeurs sont des boutons cote a cote,
 * cliquables d'un coup, au lieu d'une liste deroulante qu'il faut ouvrir.
 * Un second clic sur le bouton actif le desactive (aucun filtre).
 *
 * @param  array<string, array{0: string, 1: \Closure(Builder): mixed}>  $choix
 *         cle => [libelle, closure appliquee a la requete]
 */
class FiltreBoutons
{
    public static function make(string $nom, string $libelle, array $choix): Filter
    {
        return Filter::make($nom)
            ->schema([
                ToggleButtons::make('valeur')->label($libelle)
                    ->options(array_map(fn (array $c) => $c[0], $choix))
                    ->inline()->grouped(),
            ])
            // Filament injecte par le nom : $query et $data, rien d'autre.
            ->query(function (Builder $query, array $data) use ($choix): Builder {
                $valeur = $data['valeur'] ?? null;

                if (blank($valeur) || ! isset($choix[$valeur])) {
                    return $query;
                }

                ($choix[$valeur][1])($query);

                return $query;
            })
            ->indicateUsing(function (array $data) use ($libelle, $choix): array {
                $valeur = $data['valeur'] ?? null;

                return filled($valeur) && isset($choix[$valeur])
                    ? [Indicator::make($libelle.' : '.$choix[$valeur][0])->removeField('valeur')]
                    : [];
            });
    }

    /** Oui / Non, le cas le plus courant. */
    public static function ouiNon(string $nom, string $libelle, \Closure $oui, \Closure $non): Filter
    {
        return self::make($nom, $libelle, ['oui' => ['Oui', $oui], 'non' => ['Non', $non]]);
    }
}
