<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\DemandeContactRequest;
use App\Services\Messagerie\DepotDemande;
use App\Support\Captcha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    public function __construct(private readonly DepotDemande $depot) {}

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

        if ($this->depot->limiteAtteinte($request->ip())) {
            return $this->echec([
                'us_message' => ['Trop de demandes envoyées. Réessayez dans une heure.'],
            ]);
        }

        $this->depot->deposer($destinataire, $request->validated(), $request->ip());

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
