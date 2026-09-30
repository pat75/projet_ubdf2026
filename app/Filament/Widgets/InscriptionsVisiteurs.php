<?php

namespace App\Filament\Widgets;

use App\Models\Visitor;

/** Comptes visiteurs ouverts, cumules depuis le 1er janvier. */
class InscriptionsVisiteurs extends ComparaisonAnnuelle
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Inscriptions de visiteurs';

    protected function cle(): string
    {
        return 'inscriptions-visiteurs';
    }

    protected function sources(): array
    {
        return [[Visitor::withTrashed(), 'created_at', null]];
    }
}
