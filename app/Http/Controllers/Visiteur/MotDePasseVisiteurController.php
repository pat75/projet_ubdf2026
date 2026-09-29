<?php

namespace App\Http\Controllers\Visiteur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Visiteur\NouveauMotDePasseRequest;
use App\Models\Visitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Reinitialisation du mot de passe d'un compte visiteur (broker
 * `visitors`). La demande part de la meme fenetre que celle des
 * creatifs : InscriptionController::motDePasseOublie() previent les deux.
 */
class MotDePasseVisiteurController extends Controller
{
    public function formulaire(Request $requete, string $jeton): View
    {
        return view('front.mot-de-passe', [
            'action' => lien('visiteur.mot-de-passe.enregistrer', ['jeton' => $jeton, 'email' => $requete->query('email')]),
        ]);
    }

    public function enregistrer(NouveauMotDePasseRequest $requete, string $jeton): RedirectResponse
    {
        $visiteur = null;

        $statut = Password::broker('visitors')->reset(
            ['email' => $requete->input('email'), 'password' => $requete->validated('password'), 'token' => $jeton],
            function (Visitor $compte, string $motDePasse) use (&$visiteur) {
                $compte->forceFill(['password' => $motDePasse])->save();
                $visiteur = $compte;
            },
        );

        abort_unless($statut === Password::PASSWORD_RESET, 410, __('Ce lien n’est plus valable.'));

        // Le lien de mail vaut preuve de possession de l'adresse.
        if (! $visiteur->email_verified_at) {
            $visiteur->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::guard('visitor')->login($visiteur, remember: true);
        $requete->session()->regenerate();

        return redirect()->to(lien('visiteur.tableau'))
            ->with('statut', __('Votre mot de passe est enregistré.'));
    }
}
