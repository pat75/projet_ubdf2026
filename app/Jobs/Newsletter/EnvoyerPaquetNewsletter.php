<?php

namespace App\Jobs\Newsletter;

use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Services\Messagerie\EnvoiCampagne;
use App\Services\Newsletter\DestinataireNewsletter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Un paquet de destinataires d'une newsletter. Decouper l'envoi evite de
 * tenir une seule tache pendant des milliers de messages, et laisse la file
 * rejouer le seul paquet qui a echoue.
 */
class EnvoyerPaquetNewsletter implements ShouldQueue
{
    use Queueable;

    /** @param  list<DestinataireNewsletter>  $destinataires */
    public function __construct(
        public readonly int $campagneId,
        public readonly array $destinataires,
    ) {}

    public function handle(EnvoiCampagne $envoi): void
    {
        $campagne = Campaign::find($this->campagneId);

        if (! $campagne) {
            return;
        }

        foreach ($this->destinataires as $destinataire) {
            $envoi->mettreEnFile($campagne, $destinataire);

            CampaignSend::where('campaign_id', $campagne->id)
                ->where('email', $destinataire->email)
                ->update(['status' => 'sent', 'sent_at' => now()]);
        }
    }
}
