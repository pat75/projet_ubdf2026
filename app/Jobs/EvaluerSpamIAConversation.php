<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Services\Messagerie\DetecteurSpamIA;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Interroge le modele de decision sur le premier message d'une conversation
 * fraichement recue, en tache de fond : aucune latence ajoutee au
 * formulaire de contact du visiteur (voir DepotDemande::deposer()).
 */
class EvaluerSpamIAConversation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public readonly Conversation $conversation)
    {
    }

    public function handle(DetecteurSpamIA $detecteur): void
    {
        if (! $detecteur->actif()) {
            return;
        }

        $premier = $this->conversation->messages()->where('from_owner', false)->oldest()->first();

        if (! $premier) {
            return;
        }

        $probabilite = $detecteur->evaluer($premier->body);

        if ($probabilite === null) {
            return;
        }

        $this->conversation->forceFill([
            'spam_ia_probabilite' => $probabilite,
            'spam_ia' => $probabilite >= $detecteur->seuil(),
        ])->save();
    }
}
