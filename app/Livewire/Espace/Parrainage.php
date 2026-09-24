<?php

namespace App\Livewire\Espace;

use App\Models\Referral;
use App\Services\Paiement\Parrainage as ServiceParrainage;
use App\Support\Marque;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;

class Parrainage extends Component
{
    public string $code = '';

    public ?string $resultat = null;

    public function utiliser(ServiceParrainage $service): void
    {
        $this->validate(['code' => 'required|string|max:40']);

        // Les codes promo sont courts : on freine leur recherche a l'aveugle.
        $cle = 'code-promo:'.Auth::id();
        if (RateLimiter::tooManyAttempts($cle, 5)) {
            $this->resultat = __('Trop d’essais. Réessayez dans une heure.');

            return;
        }
        RateLimiter::hit($cle, 3600);

        $this->resultat = $service->utiliserCode(Auth::user(), $this->code);
        $this->reset('code');
    }

    public function render(ServiceParrainage $service): View
    {
        return view('livewire.espace.parrainage', [
            // La marque est partagee aux vues par ResoudreMarque, mais une
            // mise a jour Livewire ne passe pas toujours par lui : le
            // composant la resout lui-meme plutot que de s'y fier.
            'marque' => Marque::depuisHote(request()->getHost()),
            'monCode' => $service->code(Auth::user()),
            'filleuls' => Referral::where('sponsor_id', Auth::id())->with('referred:id,login,firstname,lastname')->latest('confirmed_at')->get(),
        ]);
    }
}
