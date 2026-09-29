<?php

namespace App\Http\Controllers\Front;

use App\Actions\Visiteur\OuvrirCompteContact;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\DemandeContactRequest;
use App\Services\Messagerie\DepotDemande;
use App\Support\Marque;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function __construct(
        private readonly DepotDemande $depot,
        private readonly OuvrirCompteContact $ouvrirCompte,
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

        if ($this->depot->limiteAtteinte($request->ip())) {
            return $this->echec([
                'us_message' => ['Trop de demandes envoyées. Réessayez dans une heure.'],
            ]);
        }

        $demande = $request->safe()->except('password');
        $ouvrirCompte = $request->veutUnCompte();

        // Case « Créer mon compte » : compte cree ou retrouve avant le depot,
        // un mot de passe refuse n'envoie rien.
        if ($ouvrirCompte) {
            try {
                $this->ouvrirCompte->executer(
                    $demande['us_mail'], $request->validated('password'), $demande['us_nom_prenom'],
                    $request->attributes->get('marque') ?? Marque::defaut(), $request->ip(),
                );
            } catch (ValidationException $e) {
                return $this->echec($e->errors());
            }
        }

        $this->depot->deposer($destinataire, $demande, $request->ip());

        return $this->succes($demande['action'], $ouvrirCompte);
    }

    /** `connecte` : une session vient d'etre ouverte, la page doit se recharger. */
    private function succes(string $action, bool $connecte = false): JsonResponse
    {
        return response()->json([
            'error' => false,
            'connecte' => $connecte,
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
