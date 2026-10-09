<?php

namespace App\Http\Controllers\Visiteur;

use App\Actions\Visiteur\CreerCompteVisiteur;
use App\Http\Controllers\Controller;
use App\Mail\BienvenueVisiteur;
use App\Http\Requests\Visiteur\InscriptionVisiteurRequest;
use App\Models\Visitor;
use App\Services\Auth\Recaptcha;
use App\Support\Marque;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Compte visiteur ouvert depuis la fenetre du memo book (coeur du
 * portail) : adresse + mot de passe, connexion immediate, arrivee sur
 * la page du memo.
 */
class InscriptionVisiteurController extends Controller
{
    public function __construct(
        private readonly Recaptcha $recaptcha,
        private readonly CreerCompteVisiteur $creer,
    ) {}

    public function creer(InscriptionVisiteurRequest $requete): JsonResponse
    {
        $cle = 'inscription-visiteur|'.$requete->ip();

        if (RateLimiter::tooManyAttempts($cle, 5)) {
            return response()->json(['message' => __('Trop de tentatives. Réessayez dans quelques minutes.')], 429);
        }
        RateLimiter::hit($cle, 600);

        if (! $this->recaptcha->valide($requete->input('g-recaptcha-response'), 'validate_captcha')) {
            return response()->json(['message' => __('Erreur de captcha — rechargez la page, svp.')], 422);
        }

        $visiteur = $this->creer->executer(
            $requete->validated('email'),
            $requete->validated('password'),
            $requete->validated('logins') ?? [],
            $requete->attributes->get('marque') ?? Marque::defaut(),
            $requete->ip(),
        );

        Auth::guard('visitor')->login($visiteur, remember: true);
        $requete->session()->regenerate();

        return response()->json(['url' => lien('memobook')]);
    }

    /** Confirmation de l'adresse, depuis le lien signe du mail de bienvenue. */
    public function confirmer(Request $requete, Visitor $visitor, string $hash): RedirectResponse
    {
        abort_unless(hash_equals(sha1($visitor->email), $hash), 403);

        if (! $visitor->email_verified_at) {
            $visitor->forceFill(['email_verified_at' => now()])->save();
        }

        $destination = auth('visitor')->id() === $visitor->id ? lien('visiteur.tableau') : lien('home');

        return redirect()->to($destination)->with('statut', __('Votre adresse est confirmée.'));
    }

    /** Renvoi du mail de confirmation, depuis le tableau de bord. */
    public function renvoyer(Request $requete): RedirectResponse
    {
        $visiteur = auth('visitor')->user();
        $cle = 'confirmation-visiteur|'.$visiteur->id;

        if (! $visiteur->email_verified_at && ! RateLimiter::tooManyAttempts($cle, 3)) {
            RateLimiter::hit($cle, 3600);
            Mail::to($visiteur->email)->send(new BienvenueVisiteur(
                $visiteur, $requete->attributes->get('marque') ?? Marque::defaut(), $this->creer->lienConfirmation($visiteur),
            ));
        }

        return back()->with('statut', __('Le mail de confirmation vient de vous être renvoyé.'));
    }
}
