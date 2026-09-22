<?php

namespace App\Services\Paiement;

use Payplug\Notification;
use Payplug\Payment;
use Payplug\Payplug;
use RuntimeException;

/**
 * Seul point de contact avec l'API Payplug : les tests la remplacent.
 */
class PasserellePayplug
{
    private ?Payplug $client = null;

    /** @return array{id: string, url: string} */
    public function creerPaiement(array $donnees): array
    {
        $paiement = Payment::create($donnees, $this->client());

        return ['id' => $paiement->id, 'url' => $paiement->hosted_payment->payment_url];
    }

    /**
     * Paiement authentifie a partir du corps d'une notification. Payplug ne
     * signe pas ses notifications : le SDK relit le paiement aupres de
     * l'API, on ne se fie donc jamais au corps recu.
     *
     * @return array{id: string, paye: bool, montant: int, metadata: array, brut: array}|null
     */
    public function lireNotification(string $corps): ?array
    {
        $ressource = Notification::treat($corps, $this->client());

        if (! $ressource instanceof \Payplug\Resource\Payment) {
            return null;
        }

        return [
            'id' => $ressource->id,
            'paye' => (bool) $ressource->is_paid,
            'montant' => (int) $ressource->amount,
            'metadata' => (array) $ressource->metadata,
            'brut' => [
                'id' => $ressource->id,
                'amount' => $ressource->amount,
                'currency' => $ressource->currency,
                'is_live' => $ressource->is_live,
                'paid_at' => $ressource->paid_at,
                'card_brand' => $ressource->card->brand ?? null,
                'card_last4' => $ressource->card->last4 ?? null,
            ],
        ];
    }

    private function client(): Payplug
    {
        $cle = config('formules.payplug.secret_key');

        if (! $cle) {
            throw new RuntimeException('PAYPLUG_SECRET_KEY manquante.');
        }

        return $this->client ??= Payplug::init(['secretKey' => $cle]);
    }
}
