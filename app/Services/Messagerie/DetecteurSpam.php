<?php

namespace App\Services\Messagerie;

use App\Models\Conversation;
use Illuminate\Support\Str;

/**
 * Marque une demande suspecte, sans jamais la rejeter.
 *
 * C'est le parti pris du legacy, et il est bon : un faux positif qui
 * supprimerait une vraie demande de commande coute beaucoup plus cher a un
 * creatif qu'un message indesirable de plus dans sa boite. La demande est
 * donc toujours enregistree ; seul son signalement change.
 *
 * Ce qui change par rapport a ub2020 : le marqueur est une colonne
 * (`conversations.is_spam`) et non plus une banniere HTML inseree en tete du
 * message, qui abimait la donnee sans pouvoir etre retiree ensuite.
 */
class DetecteurSpam
{
    public function estSuspecte(string $email, ?string $ip, string $message): bool
    {
        $email = Str::lower(trim($email));

        if ($this->dansListe($email, 'liste_blanche')) {
            return false;
        }

        if ($this->dansListe($email, 'emails')) {
            return true;
        }

        if ($ip !== null && in_array($ip, config('messagerie.spam.ips', []), true)) {
            return true;
        }

        foreach (config('messagerie.spam.expressions', []) as $expression) {
            if (Str::contains($message, $expression, ignoreCase: true)) {
                return true;
            }
        }

        return $this->tropDeDemandes($email);
    }

    /** Meme adresse, trop de demandes sur la fenetre glissante. */
    private function tropDeDemandes(string $email): bool
    {
        $seuil = (int) config('messagerie.spam.seuil_repetition');
        $heures = (int) config('messagerie.spam.fenetre_heures');

        if ($seuil <= 0) {
            return false;
        }

        return Conversation::withTrashed()
            ->where('sender_email', $email)
            ->where('created_at', '>=', now()->subHours($heures))
            ->count() >= $seuil;
    }

    private function dansListe(string $email, string $liste): bool
    {
        foreach (config('messagerie.spam.'.$liste, []) as $connue) {
            if (hash_equals(Str::lower(trim($connue)), $email)) {
                return true;
            }
        }

        return false;
    }
}
