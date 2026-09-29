<?php

namespace App\Http\Controllers\Front;

use App\Actions\Auth\IdentifierCompte;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ConnexionRequest;
use App\Models\Visitor;
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

    /**
     * Duree du blocage apres la derniere tentative.
     *
     * Dix minutes : assez long pour qu'une attaque par dictionnaire n'ait
     * plus de debit utile, assez court pour qu'un creatif qui s'est trompe
     * cinq fois n'ait pas a ecrire au support.
     */
    private const BLOCAGE_SECONDES = 600;

    public function __construct(
        private readonly Recaptcha $recaptcha,
        private readonly IdentifierCompte $identifier,
    ) {}

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
            return $this->echec($requete, $this->messageAttente(RateLimiter::availableIn($cle)));
        }

        if (! $this->recaptcha->valide($requete->input('g-recaptcha-response'), 'validate_captcha')) {
            RateLimiter::hit($cle, self::BLOCAGE_SECONDES);

            return $this->echec($requete, __('Erreur de captcha — rechargez la page, svp.'));
        }

        $compte = $this->identifier->executer((string) $requete->input('login'), (string) $requete->input('pass'));

        if ($compte === IdentifierCompte::AMBIGU) {
            RateLimiter::hit($cle, self::BLOCAGE_SECONDES);

            return $this->echec($requete, __('Plusieurs books utilisent cette adresse : connectez-vous avec votre identifiant.'));
        }

        if ($compte === IdentifierCompte::BLOQUE) {
            return $this->echec($requete, __('Ce compte a été suspendu. Contactez-nous pour en connaître la raison.'));
        }

        if ($compte === null) {
            RateLimiter::hit($cle, self::BLOCAGE_SECONDES);

            /*
             | Un seul message pour « identifiant inconnu » et « mot de passe
             | faux » : distinguer les deux revient a confirmer l'existence
             | d'un compte a qui le demande.
             */
            return $this->echec($requete, __('Identifiant ou mot de passe incorrect.'));
        }

        RateLimiter::clear($cle);

        // Se connecter sous l'un ferme l'autre (AppServiceProvider, evenement Login).
        if ($compte instanceof Visitor) {
            Auth::guard('visitor')->login($compte, remember: true);
            $requete->session()->regenerate();
            // La page memorisee avant connexion peut etre une page de l'espace creatif.
            $requete->session()->forget('url.intended');

            return redirect()->to(lien('visiteur.tableau'));
        }

        Auth::guard('web')->login($compte, remember: true);
        $requete->session()->regenerate();

        return redirect()->intended(lien('espace'));
    }

    public function deconnecter(Request $requete): RedirectResponse
    {
        Auth::guard('web')->logout();
        Auth::guard('visitor')->logout();
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

    /**
     * « Reessayez dans 8 minutes » plutot que « dans 487 secondes » : le
     * blocage se compte en minutes, l'annoncer en secondes donne un nombre
     * que personne ne lit.
     */
    private function messageAttente(int $secondes): string
    {
        if ($secondes < 60) {
            return __('Trop de tentatives. Réessayez dans :secondes secondes.', [
                'secondes' => $secondes,
            ]);
        }

        return trans_choice(
            'Trop de tentatives. Réessayez dans une minute.|Trop de tentatives. Réessayez dans :minutes minutes.',
            $minutes = (int) ceil($secondes / 60),
            ['minutes' => $minutes],
        );
    }

    private function cleLimitation(ConnexionRequest $requete): string
    {
        return 'connexion|'.Str::lower((string) $requete->input('login')).'|'.$requete->ip();
    }
}
