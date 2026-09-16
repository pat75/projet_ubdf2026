<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Auth\MotDePasse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Reinitialisation du mot de passe depuis le lien recu par mail.
 *
 * C'est la page qui remplace l'envoi du mot de passe en clair pratique par
 * le legacy — voir App\Services\Auth\MotDePasse.
 */
class MotDePasseController extends Controller
{
    public function __construct(private readonly MotDePasse $motDePasse) {}

    public function formulaire(int $demande, string $jeton): View
    {
        abort_unless($this->motDePasse->retrouver($demande, $jeton), 410, __('Ce lien n’est plus valable.'));

        return view('front.mot-de-passe', compact('demande', 'jeton'));
    }

    public function enregistrer(Request $requete, int $demande, string $jeton): RedirectResponse
    {
        $reinit = $this->motDePasse->retrouver($demande, $jeton);

        abort_unless($reinit, 410, __('Ce lien n’est plus valable.'));

        $requete->validate([
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $compte = $this->motDePasse->reinitialiser($reinit, $requete->input('password'));

        // Le lien de mail vaut preuve de possession de l'adresse : la
        // connexion suit, sans redemander le mot de passe qu'on vient de poser.
        Auth::login($compte);
        $requete->session()->regenerate();

        return redirect()->to(lien('espace'))
            ->with('statut', __('Votre mot de passe est enregistré.'));
    }
}
