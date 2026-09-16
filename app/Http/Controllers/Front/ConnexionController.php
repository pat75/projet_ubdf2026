<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ConnexionRequest;
use App\Services\Auth\Recaptcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Connexion et deconnexion au portail.
 *
 * Les URL du legacy sont conservees (`/ubaction__user_open`,
 * `/ubaction__user_out`) : le formulaire de la fenetre modale est repris
 * tel quel du front de 2019 et poste a ce chemin ecrit en dur.
 */
class ConnexionController extends Controller
{
    /** Tentatives autorisees avant blocage, par couple identifiant + IP. */
    private const ESSAIS_MAX = 5;

    private const BLOCAGE_SECONDES = 60;

    public function __construct(private readonly Recaptcha $recaptcha) {}

    public function connecter(ConnexionRequest $requete): RedirectResponse
    {
        $cle = $this->cleLimitation($requete);

        /*
         | Le legacy decrementait un compteur en session (`$_SESSION['us_essai']--`)
         | sans jamais s'en servir pour bloquer quoi que ce soit, et une
         | session se jette. Le comptage se fait ici cote serveur, sur le
         | couple identifiant + IP.
         */
        if (RateLimiter::tooManyAttempts($cle, self::ESSAIS_MAX)) {
            return $this->echec($requete, __('Trop de tentatives. Réessayez dans :secondes secondes.', [
                'secondes' => RateLimiter::availableIn($cle),
            ]));
        }

        if (! $this->recaptcha->valide($requete->input('g-recaptcha-response'), 'validate_captcha')) {
            RateLimiter::hit($cle, self::BLOCAGE_SECONDES);

            return $this->echec($requete, __('Erreur de captcha — rechargez la page, svp.'));
        }

        $identifiants = [
            'login' => mb_strtolower(trim($requete->input('login'))),
            'password' => $requete->input('pass'),
        ];

        if (! Auth::attempt($identifiants, remember: true)) {
            RateLimiter::hit($cle, self::BLOCAGE_SECONDES);

            /*
             | Un seul message pour « identifiant inconnu » et « mot de passe
             | faux » : distinguer les deux revient a confirmer l'existence
             | d'un compte a qui le demande.
             */
            return $this->echec($requete, __('Identifiant ou mot de passe incorrect.'));
        }

        RateLimiter::clear($cle);
        $requete->session()->regenerate();

        return redirect()->intended(lien('espace'));
    }

    public function deconnecter(Request $requete): RedirectResponse
    {
        Auth::logout();
        $requete->session()->invalidate();
        $requete->session()->regenerateToken();

        return redirect()->to(lien('accueil'));
    }

    /**
     * Renvoie sur la page d'origine, la fenetre de connexion rouverte.
     *
     * `connexion_ouverte` est lu par le gabarit : sans lui, l'erreur
     * s'afficherait dans une fenetre fermee.
     */
    private function echec(ConnexionRequest $requete, string $message): RedirectResponse
    {
        return back()
            ->withInput($requete->only('login'))
            ->with('connexion_ouverte', true)
            ->withErrors(['login' => $message]);
    }

    private function cleLimitation(ConnexionRequest $requete): string
    {
        return 'connexion|'.Str::lower((string) $requete->input('login')).'|'.$requete->ip();
    }
}
