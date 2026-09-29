<?php

namespace App\Services\Messagerie;

use App\Jobs\Newsletter\EnvoyerPaquetNewsletter;
use App\Mail\CampagneMail;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\User;
use App\Services\Newsletter\Desabonnement;
use App\Services\Newsletter\DestinataireNewsletter;
use App\Services\Newsletter\Destinataires;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

/**
 * Envoi d'une newsletter (nl_masse_mail_v2.php du legacy, lance a la main
 * par tranches depuis une page d'administration).
 *
 * Chaque destinataire a sa ligne dans `campaign_sends` avant la mise en
 * file : relancer un envoi interrompu ne redonne pas le message a ceux qui
 * l'ont deja recu. La contrainte d'unicite (campagne, adresse) en repond
 * meme si deux envois se croisent.
 */
class EnvoiCampagne
{
    /** Destinataires traites par job : de quoi lisser la charge du relais. */
    public const PAQUET = 100;

    public function __construct(
        private readonly Destinataires $destinataires,
        private readonly Desabonnement $desabonnement,
    ) {}

    /** @return int nombre de messages mis en file */
    public function envoyer(Campaign $campagne): int
    {
        $nombre = 0;

        $this->destinataires->pour($campagne)
            ->chunk(self::PAQUET)
            ->each(function (Collection $paquet) use ($campagne, &$nombre) {
                $restants = $this->reserver($campagne, $paquet);

                if ($restants->isEmpty()) {
                    return;
                }

                EnvoyerPaquetNewsletter::dispatch($campagne->id, $restants->values()->all());
                $nombre += $restants->count();
            });

        $campagne->update([
            'sent_at' => $campagne->sent_at ?? now(),
            'stats' => ['destinataires' => $nombre] + (array) $campagne->stats,
        ]);

        return $nombre;
    }

    /**
     * Pose la ligne d'envoi de chaque destinataire, et ne rend que ceux qui
     * n'en avaient pas : c'est ce qui rend un envoi rejouable.
     *
     * @param  Collection<int, DestinataireNewsletter>  $paquet
     * @return Collection<int, DestinataireNewsletter>
     */
    private function reserver(Campaign $campagne, Collection $paquet): Collection
    {
        return $paquet->filter(function (DestinataireNewsletter $destinataire) use ($campagne) {
            $ligne = CampaignSend::firstOrCreate(
                ['campaign_id' => $campagne->id, 'email' => $destinataire->email],
                [
                    'user_id' => $destinataire->userId,
                    'source' => $destinataire->source,
                    'status' => 'queued',
                    'sent_at' => now(),
                ],
            );

            return $ligne->wasRecentlyCreated;
        });
    }

    /** Met un destinataire en file, sans rien enregistrer de plus. */
    public function mettreEnFile(Campaign $campagne, DestinataireNewsletter $destinataire): void
    {
        Mail::to($destinataire->email)->queue(new CampagneMail(
            $campagne,
            $destinataire,
            $this->desabonnement->lien($destinataire->email),
        ));
    }

    /** Envoi d'essai, a une adresse choisie, sans rien enregistrer. */
    public function essai(Campaign $campagne, string $adresse, ?User $exemple = null): void
    {
        $destinataire = new DestinataireNewsletter(
            email: $adresse,
            source: 'essai',
            prenom: $exemple?->firstname,
            userId: $exemple?->id,
        );

        $this->mettreEnFile($campagne, $destinataire);

        $campagne->update(['essai_at' => now()]);
    }
}
