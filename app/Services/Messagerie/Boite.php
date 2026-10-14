<?php

namespace App\Services\Messagerie;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Boite de reception d'un creatif : partagee par l'espace (Livewire
 * Messages) et l'API des applications. La reponse elle-meme passe par
 * Intermediation.
 */
class Boite
{
    public const DOSSIERS = ['contacts', 'poubelle'];

    /**
     * `conversations()` est une relation HasMany : Eloquent la fait
     * passer pour un Builder une fois une premiere portee posee, mais son
     * type declare reste HasMany — d'ou l'absence de type de retour ici.
     */
    public function parDossier(User $creatif, string $dossier)
    {
        // Les indesirables restent dans la liste, signales par un badge :
        // le createur doit les voir pour les supprimer (ou les garder).
        $base = $creatif->conversations();

        return match ($dossier) {
            'poubelle' => $base->onlyTrashed(),
            default => $base,
        };
    }

    /** Demandes non lues d'un dossier, hors indesirables : la pastille ne reclame pas l'attention pour un spam. */
    public function nonLues(User $creatif, string $dossier = 'contacts'): int
    {
        return $this->parDossier($creatif, $dossier)
            ->where('is_spam', false)
            ->where(fn (Builder $q) => $q->where('spam_ia', false)->orWhereNull('spam_ia'))
            ->whereHas('messages', fn (Builder $m) => $m->where('from_owner', false)->whereNull('read_at'))
            ->count();
    }

    /** Demandes indesirables du dossier Contacts (administration ou IA). */
    public function indesirables(User $creatif)
    {
        return $this->parDossier($creatif, 'contacts')
            ->where(fn (Builder $q) => $q->where('is_spam', true)->orWhere('spam_ia', true));
    }

    public function conversation(User $creatif, int $id): Conversation
    {
        return $creatif->conversations()->withTrashed()->findOrFail($id);
    }

    public function marquerLue(Conversation $conversation): void
    {
        $conversation->messages()->where('from_owner', false)->whereNull('read_at')->update(['read_at' => now()]);
    }

    /** Suppression douce, ou retour de la poubelle. */
    public function basculerCorbeille(Conversation $conversation): void
    {
        $conversation->trashed() ? $conversation->restore() : $conversation->delete();
    }

    /** Supprime pour de bon toutes les demandes de la poubelle. */
    public function viderCorbeille(User $creatif): void
    {
        $creatif->conversations()->onlyTrashed()->get()
            ->each(fn (Conversation $conversation) => $conversation->forceDelete());
    }

    /** Retire le signalement d'une demande, qu'il vienne de l'administration ou de l'IA. */
    public function rendreLegitime(Conversation $conversation): void
    {
        $conversation->forceFill(['is_spam' => false, 'spam_ia' => false])->save();
    }

    public function supprimerIndesirables(User $creatif): void
    {
        $this->indesirables($creatif)->get()->each(fn (Conversation $conversation) => $conversation->delete());
    }

    /**
     * Un expediteur (ou le createur) qui renvoie plusieurs fois d'affilee
     * exactement le meme texte — un double clic sur « Envoyer », un souci
     * de connexion — n'affiche qu'une seule fois : seule une reponse au
     * contenu different rouvre une bulle.
     */
    public function sansDoublons(EloquentCollection $messages): EloquentCollection
    {
        $messages = $messages->values();

        return $messages->filter(function (Message $message, int $i) use ($messages) {
            $precedent = $messages->get($i - 1);

            return ! $precedent || $precedent->from_owner !== $message->from_owner || $precedent->body !== $message->body;
        })->values();
    }
}
