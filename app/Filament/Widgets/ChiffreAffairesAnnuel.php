<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;

/**
 * Chiffre d'affaires encaisse, cumule depuis le 1er janvier.
 *
 * Montants TTC (`invoices.amount` porte le total, `vat` n'en est que la
 * part de taxe) et factures payees seulement : une facture emise mais
 * jamais reglee n'est pas du chiffre d'affaires.
 */
class ChiffreAffairesAnnuel extends ComparaisonAnnuelle
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Chiffre d’affaires TTC';

    protected function cle(): string
    {
        return 'ca';
    }

    protected function sources(): array
    {
        return [[Invoice::query()->where('status', 'paid'), 'issued_at', 'amount']];
    }

    protected function estMontant(): bool
    {
        return true;
    }
}
