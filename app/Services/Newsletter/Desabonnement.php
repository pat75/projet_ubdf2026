<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterMail;
use App\Models\User;
use Illuminate\Support\Facades\URL;

/**
 * Desabonnement par adresse, valable pour les deux publics : le createur
 * abonne (`book_settings.diffuse_newsletter`) comme l'adresse inscrite
 * depuis le portail (`newsletter_mails`).
 *
 * Le lien est signe : aucun jeton a stocker, la signature prouve l'origine.
 * L'adresse y voyage en base64 d'URL, pour qu'un point ou un « + » ne
 * coupe pas le segment.
 */
class Desabonnement
{
    public function lien(string $email, string $action = 'newsletter.desinscription'): string
    {
        return URL::signedRoute($action, ['adresse' => $this->encoder($email)]);
    }

    public function encoder(string $email): string
    {
        return rtrim(strtr(base64_encode($email), '+/', '-_'), '=');
    }

    public function decoder(string $jeton): string
    {
        return mb_strtolower((string) base64_decode(strtr($jeton, '-_', '+/'), true));
    }

    /** @return bool vrai si l'adresse etait connue quelque part */
    public function desabonner(string $email): bool
    {
        $touche = false;

        foreach (User::where('email', $email)->get() as $creatif) {
            $creatif->bookSetting()->updateOrCreate([], [
                'diffuse_newsletter' => false,
                'newsletter_desabonne_at' => now(),
            ]);
            $touche = true;
        }

        $touche = NewsletterMail::where('email', $email)
            ->update(['desabonne_at' => now()]) > 0 || $touche;

        return $touche;
    }

    /** Retour en arriere depuis la page de desinscription. */
    public function reabonner(string $email): bool
    {
        $touche = false;

        foreach (User::where('email', $email)->get() as $creatif) {
            $creatif->bookSetting()->updateOrCreate([], [
                'diffuse_newsletter' => true,
                'newsletter_desabonne_at' => null,
            ]);
            $touche = true;
        }

        $touche = NewsletterMail::where('email', $email)
            ->update(['desabonne_at' => null]) > 0 || $touche;

        return $touche;
    }
}
