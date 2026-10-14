<?php

namespace App\Services\Paiement;

use App\Jobs\CommanderDomaine;
use App\Models\Domaine;
use App\Models\Invoice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Paiement Payplug d'un nom de domaine, avant toute commande chez OVH.
 *
 * Le paiement n'est tenu pour fait que relu aupres de Payplug (notification
 * serveur, ou relecture au retour du createur) : jamais sur la foi du
 * navigateur. Une fois paye, la facture est emise et la commande OVH part
 * en file d'attente (CommanderDomaine).
 */
class PaiementDomaine
{
    /** metadata.product_id des paiements de domaine (1 = formule). */
    public const PRODUIT = 2;

    public function __construct(
        private readonly PasserellePayplug $payplug,
        private readonly Souscription $souscription,
    ) {}

    /** URL de la page de paiement Payplug. */
    public function commencer(Domaine $domaine): string
    {
        $titulaire = $domaine->titulaire;

        $paiement = $this->payplug->creerPaiement([
            'amount' => (int) round((float) $domaine->prix_ttc * 100),
            'currency' => 'EUR',
            'billing' => $client = [
                'email' => $titulaire['email'],
                'first_name' => $titulaire['prenom'],
                'last_name' => $titulaire['nom'],
                'address1' => $titulaire['adresse'],
                'postcode' => $titulaire['code_postal'],
                'city' => $titulaire['ville'],
                'country' => $titulaire['pays'],
                'language' => app()->getLocale() === 'en' ? 'en' : 'fr',
            ],
            'shipping' => $client + ['delivery_type' => 'DIGITAL_GOODS'],
            'metadata' => [
                'customer_id' => $domaine->user_id,
                'product_id' => self::PRODUIT,
                'domaine_id' => $domaine->id,
            ],
            'hosted_payment' => [
                'return_url' => route('espace.formule', ['domaine' => 'retour']),
                'cancel_url' => route('espace.formule'),
            ],
            'notification_url' => route('payplug.notification'),
        ]);

        $domaine->update(['payplug_id' => $paiement['id']]);

        return $paiement['url'];
    }

    /** Retour du createur : relit le paiement chez Payplug (utile aussi sans notification, en local). */
    public function confirmer(Domaine $domaine): void
    {
        if ($domaine->statut === 'a_payer' && $domaine->payplug_id) {
            $this->traiter($this->payplug->lirePaiement($domaine->payplug_id));
        }
    }

    /**
     * Paiement relu chez Payplug : facture, statut « paye », commande OVH.
     * Rejouer un meme paiement ne fait rien de plus.
     *
     * @param  array{id: string, paye: bool, montant: int, metadata: array, brut: array}  $p
     */
    public function traiter(array $p): ?Domaine
    {
        $domaine = Domaine::find($p['metadata']['domaine_id'] ?? 0);

        if (! $p['paye'] || ! $domaine
            || (int) ($p['metadata']['customer_id'] ?? 0) !== $domaine->user_id
            || $p['montant'] !== (int) round((float) $domaine->prix_ttc * 100)) {
            Log::warning('Payplug domaine : paiement incoherent', ['id' => $p['id'], 'montant' => $p['montant'], 'metadata' => $p['metadata']]);

            return null;
        }

        if (app()->isProduction() && ! ($p['brut']['is_live'] ?? false)) {
            Log::warning('Payplug domaine : paiement de test recu en production', ['id' => $p['id']]);

            return null;
        }

        $paye = Cache::lock('payplug:'.$p['id'], 30)->block(10, fn () => DB::transaction(function () use ($p, $domaine) {
            $domaine = Domaine::lockForUpdate()->find($domaine->id);

            if ($domaine->statut !== 'a_payer') {
                return false;
            }

            $creatif = $domaine->user;
            $marque = $creatif->brand ?: 'ub';
            $ttc = (float) $domaine->prix_ttc;

            $facture = $creatif->invoices()->create([
                'brand' => $marque,
                'number' => 'tmp-'.$p['id'],
                'label' => $marque === 'df' ? 'Dustfolio' : 'Ultra-book',
                'designation' => __('Nom de domaine :nom (1 an)', ['nom' => $domaine->nom]),
                'amount' => $ttc,
                'vat' => round($ttc - $ttc / (1 + config('domaines.tva')), 2),
                'currency' => 'EUR',
                'status' => 'paid',
                'gateway' => 'payplug',
                'gateway_payload' => $p['brut'],
                'issued_at' => now(),
                'paid_at' => now(),
            ]);
            $facture->update(['number' => $this->souscription->prochainNumero($marque)]);

            $domaine->update(['statut' => 'paye', 'payplug_id' => $p['id'], 'invoice_id' => $facture->id]);

            return true;
        }));

        if ($paye) {
            // En local, pas de worker de file : la commande (simulee) part tout de suite.
            app()->isLocal() ? CommanderDomaine::dispatchSync($domaine->fresh()) : CommanderDomaine::dispatch($domaine->fresh());
        }

        return $domaine->fresh();
    }
}
