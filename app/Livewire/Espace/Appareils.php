<?php

namespace App\Livewire\Espace;

use App\Services\Api\ConnexionParCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Applications connectees au compte (jetons d'API) : liste, revocation,
 * et validation du code affiche par une application desktop.
 */
class Appareils extends Component
{
    public string $code = '';

    public ?string $autorise = null;

    /** Demande en attente de confirmation : nom de l'appareil et droits. */
    public ?array $demande = null;

    /** Etape 1 : le code est verifie, la demande affichee. Rien n'est encore accorde. */
    public function verifier(ConnexionParCode $connexion): void
    {
        $this->validate(['code' => ['required', 'string', 'max:20']]);
        $this->reset('autorise', 'demande');

        // Essais de codes au hasard : 10 par quart d'heure.
        $cle = 'code-appareil|'.Auth::id();
        abort_if(RateLimiter::tooManyAttempts($cle, 10), 429);
        RateLimiter::hit($cle, 900);

        $this->demande = $connexion->consulter($this->code);

        if ($this->demande === null) {
            $this->addError('code', __('Code inconnu ou expiré. Demandez-en un nouveau dans l’application.'));
        }
    }

    /** Etape 2 : le creatif confirme l'appareil et les droits affiches. */
    public function autoriser(ConnexionParCode $connexion): void
    {
        $appareil = $connexion->autoriser(Auth::user(), $this->code);
        $this->reset('demande');

        if ($appareil === null) {
            $this->addError('code', __('Code inconnu ou expiré. Demandez-en un nouveau dans l’application.'));

            return;
        }

        $this->autorise = $appareil;
        $this->reset('code');
    }

    public function annuler(): void
    {
        $this->reset('code', 'demande');
    }

    public function revoquer(int $id): void
    {
        // Limite au creatif : l'identifiant d'un jeton d'autrui ne trouve rien.
        Auth::user()->tokens()->whereKey($id)->delete();
    }

    public function render(): View
    {
        return view('livewire.espace.appareils', [
            'jetons' => Auth::user()->tokens()->latest('last_used_at')->latest()->get(),
        ]);
    }
}
