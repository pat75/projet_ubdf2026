<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Demande recue. Jamais les jetons de reponse (owner/sender_token) ni l'IP. */
class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'objet' => $this->objet(),
            'expediteur' => $this->sender_name,
            'societe' => $this->sender_company,
            'email' => $this->sender_email,
            'telephone' => $this->sender_phone,
            'detail' => $this->request_detail,
            'indesirable' => $this->is_spam || $this->spam_ia,
            'dernier_message_le' => $this->last_message_at?->toIso8601String(),
            'non_lus' => $this->whenHas('non_lus'),
            'a_la_poubelle' => $this->trashed(),
            'messages' => $this->whenLoaded('messages', fn () => $this->messages->map(fn ($m) => [
                'id' => $m->id,
                'du_creatif' => $m->from_owner,
                'texte' => $m->body,
                'envoye_le' => $m->created_at?->toIso8601String(),
                'lu_le' => $m->read_at?->toIso8601String(),
            ])),
        ];
    }
}
