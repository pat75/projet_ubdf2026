<?php

namespace App\Jobs;

use App\Models\Domaine;
use App\Services\Domaines\Ovh;
use App\Services\Paiement\PasserellePayplug;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * Commande chez OVH d'un domaine deja paye par le createur (PaiementDomaine).
 * Le pointage DNS suit quand OVH l'a livre (ubdf:verifier-domaines).
 *
 * Pas de nouvel essai automatique : une commande a moitie passee ne doit
 * pas etre payee deux fois. Si OVH refuse, le createur est rembourse.
 */
class CommanderDomaine implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public Domaine $domaine) {}

    public function handle(Ovh $ovh, PasserellePayplug $payplug): void
    {
        $domaine = $this->domaine->fresh();

        if ($domaine->statut !== 'paye') {
            return;
        }

        try {
            $ovh->commander($domaine, $domaine->titulaire);
        } catch (RuntimeException $e) {
            $domaine->update(['statut' => 'echec_commande']);
            Log::error('Commande OVH en echec', ['domaine' => $domaine->nom, 'erreur' => $e->getMessage()]);
            $this->rembourser($domaine, $payplug);

            return;
        }

        $domaine->update([
            'statut' => 'commande',
            'reserve_le' => now(),
            'expire_le' => now()->addYear(),
        ]);
    }

    private function rembourser(Domaine $domaine, PasserellePayplug $payplug): void
    {
        try {
            $payplug->rembourser($domaine->payplug_id);
            $domaine->update(['statut' => 'rembourse']);
        } catch (\Throwable $e) {
            // Remboursement a faire a la main : l'administration est prevenue.
            report($e);
        }

        Mail::raw(
            __('La réservation de :nom n’a pas pu aboutir chez notre registrar. Vous êtes intégralement remboursé.', ['nom' => $domaine->nom]),
            fn ($m) => $m->to($domaine->titulaire['email'] ?? $domaine->user->email)
                ->bcc(config('mail.from.address'))
                ->subject(__('Réservation de :nom annulée', ['nom' => $domaine->nom]))
        );
    }
}
