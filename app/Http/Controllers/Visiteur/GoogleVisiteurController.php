<?php

namespace App\Http\Controllers\Visiteur;

use App\Actions\Visiteur\CreerCompteVisiteur;
use App\Http\Controllers\Controller;
use App\Models\Reglage;
use App\Models\User;
use App\Models\Visitor;
use App\Support\Marque;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Compte visiteur par Google, depuis la fenetre « Gardez votre mémoBook ».
 *
 * Le book en attente reste dans le stockage du navigateur pendant
 * l'aller-retour chez Google : le store memo (visionneuse.js) le verse
 * dans le compte au chargement du memo, une fois connecte.
 */
class GoogleVisiteurController extends Controller
{
    public function __construct(private readonly CreerCompteVisiteur $creer) {}

    public function redirection(): RedirectResponse
    {
        if (blank(config('services.google.client_id')) || ! Reglage::googleActif()) {
            return $this->echec(__('La connexion par Google n’est pas disponible pour le moment.'));
        }

        return $this->pilote()->redirect();
    }

    public function retour(Request $requete): RedirectResponse
    {
        if (! Reglage::googleActif()) {
            return $this->echec(__('La connexion par Google n’est pas disponible pour le moment.'));
        }

        try {
            $google = $this->pilote()->user();
        } catch (Throwable) {
            return $this->echec(__('La connexion par Google a échoué. Réessayez, ou utilisez votre mot de passe.'));
        }

        $email = mb_strtolower(trim((string) $google->getEmail()));
        $verifieeGoogle = filter_var($google->user['email_verified'] ?? false, FILTER_VALIDATE_BOOL);

        $visiteur = Visitor::query()->where('google_id', $google->getId())->first();

        if (! $visiteur && $email !== '') {
            // Meme regle que l'inscription par formulaire : un visiteur ne
            // prend pas l'adresse d'un createur.
            if (User::query()->where('email', $email)->exists()) {
                return $this->echec(__('Cette adresse est celle d’un book : connectez-vous avec votre identifiant et votre mot de passe.'));
            }

            $visiteur = Visitor::query()->where('email', $email)->first();

            // Rattacher Google ouvre ce compte : adresse prouvee des deux cotes.
            if ($visiteur && (! $verifieeGoogle || $visiteur->email_verified_at === null)) {
                return $this->echec(__('Connectez-vous avec votre adresse et votre mot de passe, puis confirmez votre adresse.'));
            }

            if ($visiteur && ! $visiteur->estBloque()) {
                $visiteur->forceFill(['google_id' => $google->getId()])->save();
            }
        }

        if ($visiteur?->estBloque()) {
            return $this->echec(__('Ce compte a été suspendu. Contactez-nous pour en connaître la raison.'));
        }

        if (! $visiteur) {
            if ($email === '' || ! $verifieeGoogle) {
                return $this->echec(__('Google n’a pas confirmé votre adresse : créez votre compte avec votre e-mail et un mot de passe.'));
            }

            $visiteur = $this->creer->executer(
                $email,
                Str::password(32), // Secours : remplacable par « mot de passe oublie ».
                [],
                $requete->attributes->get('marque') ?? Marque::defaut(),
                $requete->ip(),
                $google->user['given_name'] ?? null,
                $google->user['family_name'] ?? null,
                verifie: true,
            );
            $visiteur->forceFill(['google_id' => $google->getId()])->save();
        }

        $visiteur->forceFill(['avatar_url' => $this->photo($google->getAvatar())])->save();

        Auth::guard('visitor')->login($visiteur, remember: true);
        $requete->session()->regenerate();

        return redirect()->to(lien('memobook'));
    }

    /** Photo Google en 256px, seulement depuis le domaine de Google. */
    private function photo(?string $url): ?string
    {
        if (! $url || ! preg_match('#^https://[a-z0-9-]+\.googleusercontent\.com/#', $url) || strlen($url) > 500) {
            return null;
        }

        return preg_replace('/=s\d+(-c)?$/', '=s256-c', $url);
    }

    /** Adresse de retour sur l'hote courant (portail Ultra-book ou Dustfolio). */
    private function pilote()
    {
        return Socialite::driver('google')->redirectUrl(url(config('services.google.redirect_visiteur')));
    }

    /** Retour au portail, fenetre du memo rouverte avec le message. */
    private function echec(string $message): RedirectResponse
    {
        return redirect()->to(lien('home'))->with('memo_compte_erreur', $message);
    }
}
