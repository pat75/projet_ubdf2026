<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\GoogleInscriptionRequest;
use App\Models\Reglage;
use App\Models\User;
use App\Services\Auth\Inscription;
use App\Support\Marque;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Connexion et creation de book par Google.
 *
 * Google n'est qu'une porte d'entree : un compte cree ainsi recoit un mot
 * de passe aleatoire, que son titulaire remplace quand il veut par « mot
 * de passe oublie ». Si Google devient indisponible, identifiant et mot de
 * passe suffisent toujours.
 */
class GoogleController extends Controller
{
    public function __construct(private readonly Inscription $inscription) {}

    public function redirection(): RedirectResponse
    {
        if (blank(config('services.google.client_id')) || ! Reglage::googleActif()) {
            return $this->echec(__('La connexion par Google n’est pas disponible pour le moment.'));
        }

        return $this->pilote()->redirect();
    }

    public function retour(Request $requete): RedirectResponse
    {
        // Masque depuis le back-office pendant un aller-retour chez Google.
        if (! Reglage::googleActif()) {
            return $this->echec(__('La connexion par Google n’est pas disponible pour le moment.'));
        }

        try {
            $google = $this->pilote()->user();
        } catch (Throwable) {
            return $this->echec(__('La connexion par Google a échoué. Réessayez, ou utilisez votre mot de passe.'));
        }

        $compte = User::query()->where('google_id', $google->getId())->first();

        if (! $compte && filled($google->getEmail())) {
            $comptes = User::query()->where('email', $google->getEmail())->limit(2)->get();

            /*
             | Plusieurs books peuvent partager une adresse (heritage du
             | legacy) : on ne choisit pas a la place du titulaire.
             */
            if ($comptes->count() > 1) {
                return $this->echec(__('Plusieurs books utilisent cette adresse : connectez-vous avec votre identifiant et votre mot de passe.'));
            }

            /*
             | Rattacher Google a un compte existant ouvre ce compte : il faut
             | que l'adresse soit prouvee des deux cotes. Sinon, un compte
             | ouvert avec l'adresse d'autrui capterait sa connexion Google.
             */
            $verifieeGoogle = filter_var($google->user['email_verified'] ?? false, FILTER_VALIDATE_BOOL);

            if ($comptes->isNotEmpty() && (! $verifieeGoogle || $comptes->first()->email_verified_at === null)) {
                return $this->echec(__('Connectez-vous avec votre identifiant et votre mot de passe, puis confirmez votre adresse.'));
            }

            $compte = $comptes->first();
            $compte?->forceFill(['google_id' => $google->getId()])->save();
        }

        if ($compte) {
            Auth::login($compte, remember: true);
            $requete->session()->regenerate();

            return redirect()->intended(lien('espace'));
        }

        // Nouveau venu : il reste a choisir l'adresse du book.
        $requete->session()->put('google.inscription', [
            'id' => $google->getId(),
            'email' => $google->getEmail(),
            'nom' => $google->getName() ?: Str::before((string) $google->getEmail(), '@'),
        ]);

        return redirect()->to(lien('inscription.page'));
    }

    public function inscrire(GoogleInscriptionRequest $requete): RedirectResponse
    {
        if (! Reglage::googleActif()) {
            $requete->session()->forget('google.inscription');

            return $this->echec(__('La connexion par Google n’est pas disponible pour le moment.'));
        }

        $google = $requete->session()->pull('google.inscription');
        $marque = $requete->attributes->get('marque') ?? Marque::defaut();

        $compte = $this->inscription->creer([
            'login' => $requete->validated('us_login'),
            'email' => $google['email'],
            // Solution de secours : remplacable par « mot de passe oublie ».
            'password' => Str::password(32),
            'nom' => $google['nom'],
            'categorie' => $requete->validated('us_type'),
        ], $marque, $requete->ip(), $requete->header('referer'));

        // Adresse deja verifiee par Google.
        $compte->forceFill(['google_id' => $google['id'], 'email_verified_at' => now()])->save();

        $this->inscription->envoyerBienvenue($compte, $marque);

        Auth::login($compte, remember: true);
        $requete->session()->regenerate();

        return redirect()->to(lien('espace'));
    }

    public function abandonner(Request $requete): RedirectResponse
    {
        $requete->session()->forget('google.inscription');

        return redirect()->to(lien('inscription.page'));
    }

    /** Adresse de retour sur l'hote courant : Ultra-book ou Dustfolio. */
    private function pilote()
    {
        return Socialite::driver('google')->redirectUrl(url(config('services.google.redirect')));
    }

    private function echec(string $message): RedirectResponse
    {
        return redirect()->to(lien('home'))
            ->with('connexion_ouverte', true)
            ->withErrors(['login' => $message]);
    }
}
