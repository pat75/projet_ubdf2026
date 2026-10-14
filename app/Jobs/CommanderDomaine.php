<?php

namespace App\Jobs;

use App\Models\Domaine;
use App\Services\Domaines\Ovh;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Achat d'un domaine chez OVH, hors requete web (plusieurs appels, paiement).
 * Le pointage DNS suit quand OVH a livre le domaine (ubdf:verifier-domaines).
 *
 * Pas de nouvel essai automatique : une commande a moitie passee ne doit
 * pas etre payee deux fois. Un echec se reprend a la main.
 */
class CommanderDomaine implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    /** @param  array<string, string|null>  $titulaire  voir Ovh::commander() */
    public function __construct(public Domaine $domaine, public array $titulaire) {}

    public function handle(Ovh $ovh): void
    {
        $ovh->commander($this->domaine, $this->titulaire);
    }
}
