<?php

namespace App\Services\Espace;

use App\Models\User;

/**
 * Code personnel de l'offre couplee avec les-illustrateurs.com.
 *
 * Le code n'est stocke nulle part : les deux sites le recalculent a
 * partir du seul identifiant et de l'annee, avec la meme cle partagee.
 * C'est la mecanique du legacy (`generateUserPromo`), reprise a
 * l'identique pour que les codes deja communiques restent valables.
 *
 * Il change donc au 1er janvier, ce qui borne l'offre a l'annee en cours.
 */
class CodeDiffusion
{
    public function pour(User $creatif, ?int $annee = null): string
    {
        $annee ??= (int) date('Y');

        $empreinte = hash_hmac('sha256', $creatif->login.$annee, (string) config('services.diffusion.cle'));

        return strtoupper(substr($empreinte, 0, 8));
    }
}
