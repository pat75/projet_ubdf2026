<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\DemandeContactRequest;
use App\Services\Messagerie\DepotDemande;
use Illuminate\Http\JsonResponse;

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
