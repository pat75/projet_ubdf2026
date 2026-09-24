<?php

namespace App\Services\Messagerie;

use App\Jobs\EvaluerSpamIAConversation;
use App\Mail\DemandeRecue;
use App\Mail\DemandeTransmise;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Depot d'une demande adressee a un createur, quel que soit le formulaire
 * d'origine : la fenetre modale du portail (/intermediate_send) ou la page
 * contact du book (/contact sur son sous-domaine).
 *
 * Le legacy avait deux chaines distinctes (ajax_2019_intermediate.php et
 * ajax_2016_contact_frombook.php), qui avaient diverge. Une seule ici.
 */
class DepotDemande
{
    public function __construct(
        private readonly Intermediation $intermediation,
        private readonly DetecteurSpam $spam,
    ) {}

    /** Limite de debit par adresse IP, commune aux deux formulaires. */
    public function limiteAtteinte(string $ip): bool
    {
        $cle = 'demande:'.$ip;

        if (RateLimiter::tooManyAttempts($cle, (int) config('messagerie.demandes_par_heure'))) {
            return true;
        }

        RateLimiter::hit($cle, 3600);

        return false;
    }

    /**
     * Enregistre la demande et notifie les deux parties.
     *
     * La demande est enregistree dans tous les cas : un faux positif ne doit
     * jamais faire disparaitre une commande. Seule la notification est
     * retenue, pour ne pas relayer le spam par courriel.
     *
     * @param  array<string, mixed>  $demande  Champs `us_*` du portail.
     */
    public function deposer(User $destinataire, array $demande, string $ip): Conversation
    {
        $ouverture = $this->intermediation->ouvrir($destinataire, $demande, $ip);
        $conversation = $ouverture['conversation'];

        if ($this->spam->estSuspecte($conversation->sender_email, $ip, $conversation->messages->first()?->body ?? '')) {
            $conversation->forceFill(['is_spam' => true])->save();

            return $conversation;
        }

        // Deja ecartee par les regles ci-dessus : inutile d'y ajouter un
        // appel IA, elle n'apparait plus dans Contacts pour porter le label.
        if (config('messagerie.spam_filter.active')) {
            EvaluerSpamIAConversation::dispatch($conversation);
        }

        Mail::to($destinataire->email)->send(
            new DemandeRecue($conversation, $ouverture['liens'][Intermediation::PROPRIETAIRE])
        );

        Mail::to($conversation->sender_email)->send(
            new DemandeTransmise($conversation, $ouverture['liens'][Intermediation::EMETTEUR])
        );

        return $conversation;
    }
}
