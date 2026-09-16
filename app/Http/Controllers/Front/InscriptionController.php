<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\InscriptionRequest;
use App\Models\User;
use App\Services\Auth\Inscription;
use App\Services\Auth\MotDePasse;
use App\Services\Auth\Recaptcha;
use App\Support\Marque;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Inscription d'un creatif et demande de mot de passe oublie.
 *
 * Ces deux formulaires partagent un point d'entree, `/inscription`, parce
 * que le JavaScript de 2019 y poste les deux et distingue le traitement par
 * le champ cache `form_id` — c'est le `switch` de front/ajax_2010.php.
 *
 * Le contrat JSON est celui que ce JavaScript attend :
 * `{error: bool, error_msg: [...]}` en echec, et en succes
 * `{error: false, url_domaine, url_action}` pour l'inscription — le script
 * y navigue ensuite (`go_to_url_valided_inscription`).
 */
class InscriptionController extends Controller
{
    public function __construct(
        private readonly Inscription $inscription,
        private readonly MotDePasse $motDePasse,
        private readonly Recaptcha $recaptcha,
    ) {}

    /**
     * Disponibilite d'un identifiant, interrogee a la frappe.
     *
     * Reponse en texte brut « true » / « false » : le script compare
     * `data == 'true'`, sans `dataType: json`.
     */
    public function loginDisponible(Request $requete): Response
    {
        $libre = $this->inscription->loginDisponible((string) $requete->query('us_login', ''));

        return response($libre ? 'true' : 'false')
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }

    /**
     * Aiguillage sur `form_id`.
     *
     * Les variantes sociales du legacy (`form_adduser_google`,
     * `_facebook`, `_linkedin`) ne sont pas reprises : la connexion par
     * reseau social reposait sur HybridAuth, abandonne, et sera reprise
     * en phase 5 si elle est conservee.
     */
    public function soumettre(Request $requete): JsonResponse
    {
        return match ((string) $requete->input('form_id')) {
            'form_adduser' => $this->garde($requete, 'inscription', 300)
                ?? $this->creer(app(InscriptionRequest::class)),
            'form_mdpoublie' => $this->garde($requete, 'mdp-oublie', 900)
                ?? $this->motDePasseOublie($requete),
            default => response()->json([
                'error' => true,
                'error_msg' => [__('Formulaire inconnu.')],
            ], 422),
        };
    }

    /**
     * Debit et captcha, avant toute ecriture.
     *
     * Retourne la reponse d'echec, ou null pour laisser passer. Ce controle
     * precede volontairement la validation : il ne sert a rien de valider
     * finement une requete automatisee.
     */
    private function garde(Request $requete, string $portee, int $fenetre): ?JsonResponse
    {
        $cle = $portee.'|'.$requete->ip();

        if (RateLimiter::tooManyAttempts($cle, 5)) {
            return $this->erreurs([__('Trop de demandes. Réessayez dans un instant.')]);
        }

        if (! $this->recaptcha->valide($requete->input('g-recaptcha-response'), 'validate_captcha')) {
            RateLimiter::hit($cle, $fenetre);

            return $this->erreurs([__('Erreur de captcha — rechargez la page, svp.')]);
        }

        return null;
    }

    private function creer(InscriptionRequest $requete): JsonResponse
    {
        RateLimiter::hit('inscription|'.$requete->ip(), 300);

        $marque = $requete->attributes->get('marque') ?? Marque::defaut();

        $compte = $this->inscription->creer([
            'login' => $requete->input('us_login'),
            'email' => $requete->input('us_mail'),
            'password' => $requete->input('us_pass'),
            'nom' => $requete->input('us_nom'),
            'categorie' => $requete->input('us_type'),
        ], $marque, $requete->ip(), $requete->header('referer'));

        $this->inscription->envoyerBienvenue($compte, $marque);

        /*
         | Le legacy renvoyait ici l'URL de confirmation par mail et y
         | envoyait le navigateur immediatement : la « confirmation » ne
         | confirmait donc rien, puisqu'elle etait franchie sans passer par
         | la boite mail. Le compte est desormais ouvert directement — le
         | creatif est connecte — et le mail de confirmation sert a ce a
         | quoi il sert vraiment : verifier qu'on peut le joindre.
         */
        Auth::login($compte, remember: true);
        $requete->session()->regenerate();

        $espace = lien('espace');

        return response()->json([
            'error' => false,
            'us_mail' => $compte->email,
            'url_domaine' => parse_url($espace, PHP_URL_HOST),
            'url_action' => ltrim((string) parse_url($espace, PHP_URL_PATH), '/'),
        ]);
    }

    private function motDePasseOublie(Request $requete): JsonResponse
    {
        RateLimiter::hit('mdp-oublie|'.$requete->ip(), 900);

        $valide = validator($requete->all(), [
            'us_mail' => ['required', 'string', 'email:rfc', 'max:255'],
        ]);

        if ($valide->fails()) {
            return $this->erreurs($valide->errors()->first('us_mail'));
        }

        $nombre = $this->motDePasse->demander($requete->input('us_mail'), $requete->ip());

        /*
         | La reponse est la meme qu'aucun compte ne corresponde ou que
         | plusieurs le fassent. Le legacy repondait « Votre mail ne
         | correspond a aucun compte », ce qui permettait de tester une
         | adresse. Le nombre de comptes trouves, lui, n'est dit que dans le
         | mail — donc au titulaire de l'adresse.
         */
        $message = $nombre > 1
            ? __('Un mail vient de vous être envoyé. Il porte un lien par compte rattaché à cette adresse.')
            : __('Si un compte utilise cette adresse, un mail vient de lui être envoyé. Pensez à regarder vos indésirables.');

        return response()->json([
            'error' => false,
            'header' => __('Récupération'),
            'msg' => $message,
            'list_mail' => '',
        ]);
    }

    /** Confirmation de l'adresse mail, depuis le lien signe du mail de bienvenue. */
    public function confirmer(Request $requete, User $user): RedirectResponse
    {
        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()->to(lien('espace'))
            ->with('statut', __('Votre adresse est confirmée.'));
    }

    /** @param  string|list<string>  $messages */
    private function erreurs(string|array $messages): JsonResponse
    {
        return response()->json([
            'error' => true,
            'error_msg' => $messages,
        ]);
    }
}
