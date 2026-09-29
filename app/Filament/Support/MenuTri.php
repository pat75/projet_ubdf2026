<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

/**
 * « Trier par » : les ordres de lecture habituels d'une liste, nommes.
 *
 * Les en-tetes de colonnes savent deja trier, mais il faut connaitre la
 * colonne qui porte l'ordre voulu — « les dernieres selections », c'est
 * `home_selection_at`, pas la colonne « Sélection ». Ce menu nomme les
 * ordres au lieu des colonnes.
 *
 * Il passe par `sortTable()`, le tri natif de la table : l'ordre choisi
 * se lit ensuite dans l'en-tete de colonne, se renverse d'un clic et
 * survit a la pagination — ce qu'un `orderBy` pose depuis un filtre ne
 * ferait pas.
 *
 * Contrainte a retenir : `sortTable()` ne s'applique qu'a une colonne
 * *visible*. Les colonnes citees ici ne doivent donc pas etre masquables.
 */
class MenuTri
{
    /**
     * @param  array<string, array{0: string, 1: string}>  $ordres
     *         Libelle => [colonne, sens].
     */
    public static function make(array $ordres): ActionGroup
    {
        $actions = [];

        foreach ($ordres as $libelle => [$colonne, $sens]) {
            $actions[] = Action::make('tri_'.$colonne.'_'.$sens)
                ->label($libelle)
                ->icon($sens === 'desc' ? 'heroicon-o-bars-arrow-down' : 'heroicon-o-bars-arrow-up')
                ->action(fn ($livewire) => $livewire->sortTable($colonne, $sens));
        }

        return ActionGroup::make($actions)
            ->label('Trier par')
            ->icon('heroicon-o-arrows-up-down')
            ->button()
            ->color('gray');
    }
}
