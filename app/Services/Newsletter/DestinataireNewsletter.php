<?php

namespace App\Services\Newsletter;

/**
 * Un destinataire de newsletter, quelle que soit sa provenance : createur
 * abonne, ou adresse laissee sur le portail. L'envoi ne connait que cela.
 */
final class DestinataireNewsletter
{
    public function __construct(
        public readonly string $email,
        /** `creatifs` ou `visiteurs` : sert au lien de desabonnement et au suivi. */
        public readonly string $source,
        public readonly ?string $prenom = null,
        public readonly ?int $userId = null,
    ) {}

    /** Prenom utilisable dans le corps du message ; vide si inconnu. */
    public function prenom(): string
    {
        return trim((string) $this->prenom);
    }
}
