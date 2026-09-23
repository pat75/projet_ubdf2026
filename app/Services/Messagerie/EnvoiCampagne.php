<?php

namespace App\Services\Messagerie;

use App\Mail\CampagneMail;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Envoi d'une campagne (nl_masse_mail_v2.php du legacy, lance a la main par
 * tranches depuis une page d'administration).
 *
 * Chaque destinataire a sa ligne dans `campaign_sends` avant la mise en
 * file : relancer un envoi interrompu ne redonne pas le message a ceux qui
 * l'ont deja recu.
 */
class EnvoiCampagne
{
    /** @return int nombre de messages mis en file */
    public function envoyer(Campaign $campagne): int
    {
        $envoyes = 0;

        $this->destinataires($campagne)->each(function (User $creatif) use ($campagne, &$envoyes) {
            $deja = CampaignSend::where('campaign_id', $campagne->id)
                ->where('user_id', $creatif->id)->exists();

            if ($deja) {
                return;
            }

            CampaignSend::create([
                'campaign_id' => $campagne->id,
                'user_id' => $creatif->id,
                'email' => $creatif->email,
                'status' => 'queued',
                'sent_at' => now(),
            ]);

            Mail::to($creatif->email)->queue(
                new CampagneMail($campagne, $creatif, $this->lienDesabonnement($creatif))
            );

            $envoyes++;
        });

        $campagne->update(['sent_at' => $campagne->sent_at ?? now()]);

        return $envoyes;
    }

    /** Envoi d'essai, a une adresse choisie, sans rien enregistrer. */
    public function essai(Campaign $campagne, string $adresse, User $exemple): void
    {
        Mail::to($adresse)->queue(new CampagneMail($campagne, $exemple, $this->lienDesabonnement($exemple)));
    }

    /**
     * Createurs de la marque qui acceptent la newsletter. Le refus se lit
     * dans `book_settings.diffuse_newsletter`, ou le lien de desabonnement
     * ecrit.
     */
    public function destinataires(Campaign $campagne)
    {
        return User::query()
            ->where('brand', $campagne->brand ?: 'ub')
            ->whereNotNull('email')
            ->whereHas('bookSetting', fn ($q) => $q->where('diffuse_newsletter', true))
            ->cursor();
    }

    /** Lien signe, sans jeton a stocker : la signature suffit a prouver l'origine. */
    public function lienDesabonnement(User $creatif): string
    {
        return URL::signedRoute('newsletter.desabonnement', ['user' => $creatif->login]);
    }
}
