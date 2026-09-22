<?php

namespace App\Services\Messagerie;

use App\Mail\ReponseRecue;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Messagerie intermediee du portail.
 *
 * Le visiteur n'obtient jamais l'adresse du creatif, et reciproquement :
 * chacun recoit un lien qui ouvre le fil de son cote. C'est la raison d'etre
 * du dispositif, et ce qui protege les creatifs du demarchage automatise.
 *
 * Chaque conversation porte donc deux acces independants. Ils sont stockes
 * haches : le lien en clair n'existe que dans le courriel envoye. Voir la
 * migration `2026_01_02_000200` pour ce que corrige ce decoupage.
 */
class Intermediation
{
    /** Les deux points de vue sur un fil. */
    public const PROPRIETAIRE = 'owner';

    public const EMETTEUR = 'sender';

    /**
     * Ouvre une conversation et y depose le premier message.
     *
     * @param  array<string, mixed>  $demande
     * @return array{conversation: Conversation, liens: array<string, string>}
     */
    public function ouvrir(User $destinataire, array $demande, ?string $ip = null): array
    {
        $jetons = [
            self::PROPRIETAIRE => $this->jeton(),
            self::EMETTEUR => $this->jeton(),
        ];

        $conversation = DB::transaction(function () use ($destinataire, $demande, $ip, $jetons) {
            $conversation = Conversation::create([
                'user_id' => $destinataire->id,
                'channel' => 'intermediate',
                'subject' => $demande['action'],
                'request_detail' => $demande['mf_request_detail'] ?? null,
                'sender_name' => $demande['us_nom_prenom'],
                'sender_company' => $demande['us_societe'] ?? null,
                'sender_email' => $demande['us_mail'],
                'sender_phone' => $demande['us_tel'] ?? null,
                'book_image' => $demande['us_book_visuel'] ?? null,
                'selector' => Str::lower(Str::random(24)),
                'owner_token' => $this->hacher($jetons[self::PROPRIETAIRE]),
                'sender_token' => $this->hacher($jetons[self::EMETTEUR]),
                'is_spam' => false,
                'last_message_at' => now(),
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'from_owner' => false,
                'body' => $demande['us_message'],
                'ip' => $ip,
            ]);

            return $conversation;
        });

        return [
            'conversation' => $conversation,
            'liens' => [
                self::PROPRIETAIRE => $this->lien($conversation, self::PROPRIETAIRE, $jetons[self::PROPRIETAIRE]),
                self::EMETTEUR => $this->lien($conversation, self::EMETTEUR, $jetons[self::EMETTEUR]),
            ],
        ];
    }

    /**
     * Retrouve une conversation depuis un lien, et le role qu'il ouvre.
     *
     * Le `selector` designe la ligne, le jeton l'authentifie : sans le
     * premier il faudrait comparer le hachage de toutes les conversations,
     * sans le second le lien serait devinable.
     *
     * @return array{conversation: Conversation, role: string}|null
     */
    public function ouvrirDepuisLien(string $role, string $selector, string $jeton): ?array
    {
        if (! in_array($role, [self::PROPRIETAIRE, self::EMETTEUR], true)) {
            return null;
        }

        $conversation = Conversation::where('selector', $selector)->first();

        if (! $conversation) {
            return null;
        }

        $attendu = (string) $conversation->getAttribute($role.'_token');

        if ($attendu === '' || ! hash_equals($attendu, $this->hacher($jeton))) {
            return null;
        }

        if ($this->expire($conversation)) {
            return null;
        }

        return ['conversation' => $conversation, 'role' => $role];
    }

    /** Ajoute une reponse au fil, du cote indique. */
    public function repondre(Conversation $conversation, string $role, string $corps, ?string $ip = null): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'from_owner' => $role === self::PROPRIETAIRE,
            'body' => $corps,
            'ip' => $ip,
        ]);

        $conversation->forceFill(['last_message_at' => now()])->save();

        return $message;
    }

    /** Previent l'autre partie d'une reponse, avec un lien renouvele vers le fil. */
    public function notifierAutrePartie(Conversation $conversation, string $role): void
    {
        $destinataire = $role === self::PROPRIETAIRE
            ? self::EMETTEUR
            : self::PROPRIETAIRE;

        $adresse = $destinataire === self::PROPRIETAIRE
            ? $conversation->user->email
            : $conversation->sender_email;

        if (! $adresse) {
            return;
        }

        $auteur = $role === self::PROPRIETAIRE
            ? $conversation->user->fullName()
            : $conversation->sender_name;

        $lien = $this->renouveler($conversation, $destinataire);

        Mail::to($adresse)->send(new ReponseRecue($conversation->fresh(['messages']), $lien, $auteur));
    }

    /**
     * Un lien n'ouvre plus un fil reste sans reponse au-dela du delai.
     *
     * Le legacy ne posait aucune limite : un lien emis en 2019 donnait
     * encore acces au fil sept ans plus tard.
     */
    public function expire(Conversation $conversation): bool
    {
        $jours = (int) config('messagerie.lien_valide_jours');

        return $jours > 0
            && $conversation->last_message_at !== null
            && $conversation->last_message_at->addDays($jours)->isPast();
    }

    /**
     * Attribue un nouveau jeton a une partie et rend son lien.
     *
     * Appele a chaque notification : le lien envoye est toujours le plus
     * recent, et celui qui a circule depuis l'ouverture du fil cesse
     * d'ouvrir quoi que ce soit.
     */
    public function renouveler(Conversation $conversation, string $role): string
    {
        $jeton = $this->jeton();

        $conversation->forceFill([$role.'_token' => $this->hacher($jeton)])->save();

        return $this->lien($conversation, $role, $jeton);
    }

    public function lien(Conversation $conversation, string $role, string $jeton): string
    {
        return route('messagerie.fil', [
            'role' => $role,
            'selector' => $conversation->selector,
            'jeton' => $jeton,
        ]);
    }

    private function jeton(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * SHA-256 sans sel : le jeton fait deja 256 bits d'entropie, un sel ne
     * protegerait de rien et empecherait la recherche par egalite.
     */
    private function hacher(string $jeton): string
    {
        return hash('sha256', $jeton);
    }
}
