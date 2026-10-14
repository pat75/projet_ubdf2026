<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReponseRequest;
use App\Http\Resources\Api\ConversationResource;
use App\Services\Messagerie\Boite;
use App\Services\Messagerie\Intermediation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/** Mes messages, pour le porteur du jeton (droit messages:*). */
class MessageController extends Controller
{
    public function __construct(private readonly Boite $boite) {}

    public function index(Request $requete): AnonymousResourceCollection
    {
        $dossier = $requete->validate(['dossier' => ['sometimes', Rule::in(Boite::DOSSIERS)]])['dossier'] ?? 'contacts';

        return ConversationResource::collection(
            $this->boite->parDossier($requete->user(), $dossier)
                ->withCount(['messages as non_lus' => fn (Builder $q) => $q->where('from_owner', false)->whereNull('read_at')])
                ->orderByDesc('last_message_at')->orderByDesc('id')
                ->paginate(20)
        )->additional(['non_lus' => $this->boite->nonLues($requete->user())]);
    }

    /** Ouvrir un fil le marque lu, comme dans l'espace. */
    public function show(Request $requete, int $id): ConversationResource
    {
        $conversation = $this->boite->conversation($requete->user(), $id);
        $this->boite->marquerLue($conversation);
        $conversation->load('messages');

        return new ConversationResource($conversation->setRelation('messages', $this->boite->sansDoublons($conversation->messages)));
    }

    /** Repond, puis previent l'emetteur par mail, comme depuis l'espace. */
    public function repondre(ReponseRequest $requete, Intermediation $intermediation, int $id): ConversationResource
    {
        $conversation = $this->boite->conversation($requete->user(), $id);
        abort_if($conversation->trashed(), 409, __('Restaurez d’abord cette demande.'));

        $intermediation->repondre($conversation, Intermediation::PROPRIETAIRE, $requete->validated('texte'), $requete->ip());
        $intermediation->notifierAutrePartie($conversation, Intermediation::PROPRIETAIRE);

        return $this->show($requete, $id);
    }

    /** Mise a la poubelle (suppression douce). */
    public function destroy(Request $requete, int $id): Response
    {
        $conversation = $this->boite->conversation($requete->user(), $id);

        if (! $conversation->trashed()) {
            $this->boite->basculerCorbeille($conversation);
        }

        return response()->noContent();
    }

    public function restaurer(Request $requete, int $id): Response
    {
        $conversation = $this->boite->conversation($requete->user(), $id);

        if ($conversation->trashed()) {
            $this->boite->basculerCorbeille($conversation);
        }

        return response()->noContent();
    }

    /** Supprime pour de bon toute la poubelle. */
    public function viderPoubelle(Request $requete): Response
    {
        $this->boite->viderCorbeille($requete->user());

        return response()->noContent();
    }
}
