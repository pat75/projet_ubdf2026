<?php

namespace App\Filament\Widgets;

use App\Models\User;

/** Comptes createurs ouverts, cumules depuis le 1er janvier. */
class InscriptionsCreatifs extends ComparaisonAnnuelle
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Inscriptions de créatifs';

    protected function cle(): string
    {
        return 'inscriptions-creatifs';
    }

    protected function sources(): array
    {
        // Comptes supprimes compris : ils se sont bien inscrits cette
        // annee-la, les retirer reecrirait le passe a chaque depart.
        return [[User::withTrashed(), 'created_at', null]];
    }
}
