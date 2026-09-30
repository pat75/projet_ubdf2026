<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Visitor;

/**
 * Comptes supprimes, createurs et visiteurs reunis dans une seule courbe.
 *
 * Suppression veut dire `deleted_at` : la corbeille du back-office, d'ou
 * un compte peut encore etre restaure. Un compte restaure sort donc du
 * graphique, ce qui est bien le comportement attendu.
 */
class Desinscriptions extends ComparaisonAnnuelle
{
    protected static ?int $sort = 5;

    // Seule sur sa ligne, sous les deux courbes d'inscriptions : c'est
    // leur contrepoint, on la lit apres elles et non a cote de l'une.
    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Désinscriptions';

    protected function cle(): string
    {
        return 'desinscriptions';
    }

    protected function sources(): array
    {
        return [
            [User::onlyTrashed(), 'deleted_at', null],
            [Visitor::onlyTrashed(), 'deleted_at', null],
        ];
    }
}
