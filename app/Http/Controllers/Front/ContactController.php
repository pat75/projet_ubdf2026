<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\DemandeContactRequest;
use App\Mail\DemandeRecue;
use App\Mail\DemandeTransmise;
use App\Services\Messagerie\DetecteurSpam;
use App\Services\Messagerie\Intermediation;
use App\Support\Captcha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function __construct(
        private readonly Intermediation $intermediation,
        private readonly DetecteurSpam $spam,
    ) {}

    /**
     * Depot d'une demande : POST /intermediate_send.
     *
     * La forme de la reponse est celle qu'attend `js_core_cards.js`
     * (`error`, `error_list`, `action`, `savedb_result`) : il la lit telle
     * quelle pour basculer entre son ecran de confirmation et sa liste
     * d'erreurs. Le code HTTP reste 200 dans les deux cas — un 422 le
     * laisserait sur son indicateur de chargement.
     */
    public function envoyer(DemandeContactRequest $request): JsonResponse
    {
        $destinataire = $request->destinataire();

        if (! $destinataire) {
            return $this->echec(['us_dir' => ['Ce book n’est pas accessible.']]);
        }

        $cle = 'demande:'.$request->ip();

        if (RateLimiter::tooManyAttempts($cle, (int) config('messagerie.demandes_par_heure'))) {
            return $this->echec([
                'us_message' => ['Trop de demandes envoyées. Réessayez dans une heure.'],
            ]);
        }

        RateLimiter::hit($cle, 3600);

        $ouverture = $this->intermediation->ouvrir(
            $destinataire,
            $request->validated(),
            $request->ip(),
        );

        $conversation = $ouverture['conversation'];

        // La demande est enregistree dans tous les cas : un faux positif ne
        // doit jamais faire disparaitre une commande. Seule la notification
        // est retenue, pour ne pas relayer le spam par courriel.
        if ($this->spam->estSuspecte($conversation->sender_email, $request->ip(), $conversation->messages->first()?->body ?? '')) {
            $conversation->forceFill(['is_spam' => true])->save();

            return $this->succes($request->validated()['action']);
        }

        Mail::to($destinataire->email)->send(
            new DemandeRecue($conversation, $ouverture['liens'][Intermediation::PROPRIETAIRE])
        );

        Mail::to($conversation->sender_email)->send(
            new DemandeTransmise($conversation, $ouverture['liens'][Intermediation::EMETTEUR])
        );

        return $this->succes($request->validated()['action']);
    }

    /**
     * Image du captcha : GET /captcha_img.
     *
     * Le legacy dessinait cinq lettres avec GD et une police au hasard. Le
     * rendu passe ici en SVG : pas de dependance a GD ni aux fichiers de
     * police, et une image que le navigateur affiche a n'importe quelle
     * definition.
     */
    public function captcha(): Response
    {
        $code = Captcha::generer();

        return response(view('front.captcha', ['code' => $code])->render(), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function succes(string $action): JsonResponse
    {
        return response()->json([
            'error' => false,
            'error_list' => [],
            'action' => str_replace(['work_A_', 'work_B_', 'work_C_'], 'work_', $action),
            'savedb_result' => true,
        ]);
    }

    /**
     * @param  array<string, list<string>>  $erreurs
     */
    private function echec(array $erreurs): JsonResponse
    {
        return response()->json([
            'error' => true,
            'error_list' => $erreurs,
            'savedb_result' => false,
        ]);
    }
}
