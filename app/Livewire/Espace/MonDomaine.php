<?php

namespace App\Livewire\Espace;

use App\Models\Domaine;
use App\Services\Domaines\Ovh;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use RuntimeException;

/**
 * Mon portfolio > Nom de domaine : informations, echeance, resiliation
 * (pas de renouvellement) et transfert (code remis au createur).
 * Reserve au developpement (Domaine::achatOuvert).
 */
class MonDomaine extends Component
{
    /** Code de transfert, affiche une seule fois apres la demande. */
    public ?string $authCode = null;

    public ?string $erreur = null;

    /** Mot « résilier » recopie par le createur avant la resiliation. */
    public string $confirmation = '';

    public function mount(): void
    {
        abort_unless(Domaine::achatOuvert() && $this->domaine(), 404);
    }

    public function resilier(Ovh $ovh): void
    {
        $domaine = $this->domaine();
        // Un transfert demande peut ne jamais aboutir : la resiliation reste possible.
        abort_unless(in_array($domaine->statut, ['commande', 'en_attente', 'dns_ok', 'actif', 'transfert'], true), 409);

        if (mb_strtolower(trim($this->confirmation)) !== mb_strtolower(__('résilier'))) {
            $this->addError('confirmation', __('Recopiez « :mot » pour confirmer.', ['mot' => __('résilier')]));

            return;
        }

        try {
            $ovh->resilier($domaine);
        } catch (RuntimeException $e) {
            report($e);
            $this->erreur = __('La résiliation n’a pas pu être enregistrée, réessayez plus tard.');

            return;
        }

        // Le domaine reste servi jusqu'a son echeance.
        $domaine->update(['statut' => 'resilie', 'resilie_le' => now()]);
    }

    public function transferer(Ovh $ovh): void
    {
        $domaine = $this->domaine();
        abort_if(in_array($domaine->statut, ['paye', 'expire'], true), 409);

        try {
            $this->authCode = $ovh->authCode($domaine);
        } catch (RuntimeException $e) {
            report($e);
            $this->erreur = __('Le code de transfert n’a pas pu être obtenu, réessayez plus tard.');

            return;
        }

        $domaine->update(['statut' => 'transfert']);
    }

    public function render(): View
    {
        return view('livewire.espace.mon-domaine', ['domaine' => $this->domaine()]);
    }

    /** Le domaine du createur connecte, paye : jamais celui d'un autre. */
    private function domaine(): ?Domaine
    {
        return Domaine::where('user_id', Auth::id())
            ->whereIn('statut', array_diff(Domaine::EN_COURS, ['a_payer']))
            ->with('facture')->latest('id')->first();
    }
}
