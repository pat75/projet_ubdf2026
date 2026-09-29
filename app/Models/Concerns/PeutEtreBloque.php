<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Compte fermable sans effacement (creatifs et visiteurs).
 *
 * Un compte bloque conserve tout — book, visuels, factures, memo book —
 * et reste listable au back-office. Seule la connexion lui est refusee,
 * par App\Http\Middleware\RefuserComptesBloques et par
 * App\Actions\Auth\IdentifierCompte.
 */
trait PeutEtreBloque
{
    public function estBloque(): bool
    {
        return $this->blocked_at !== null;
    }

    public function bloquer(?string $motif = null): void
    {
        $this->forceFill([
            'blocked_at' => now(),
            'blocked_reason' => $motif ?: null,
        ])->save();
    }

    public function debloquer(): void
    {
        $this->forceFill(['blocked_at' => null, 'blocked_reason' => null])->save();
    }

    public function scopeBloques(Builder $requete): Builder
    {
        return $requete->whereNotNull('blocked_at');
    }

    public function scopeActifs(Builder $requete): Builder
    {
        return $requete->whereNull('blocked_at');
    }
}
