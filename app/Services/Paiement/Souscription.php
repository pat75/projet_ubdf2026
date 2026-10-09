<?php

namespace App\Services\Paiement;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Souscription et renouvellement des formules par Payplug
 * (user_formule::payplug_lightbox / payplug_ipn du legacy).
 */
class Souscription
{
    public function __construct(
        private readonly PasserellePayplug $payplug,
        private readonly Promotions $promotions,
    ) {}

    /**
     * Options proposees a ce createur, dans l'ordre de la grille : le
     * reabonnement remplace le 12 mois des qu'une facture a ete payee, et une
     * promotion en cours remplace l'option qu'elle vise.
     *
     * @return array<int, array>
     */
    public function options(User $creatif): array
    {
        $grille = collect(config('formules.options'));
        $dejaAbonne = $this->dejaAbonne($creatif);

        $actives = $grille->filter(fn ($o) => match ($o['promo'] ?? null) {
            'blackfriday' => $this->promotions->blackFriday(),
            'promo-auto-6mois' => $this->promotions->promo6Mois($creatif),
            default => false,
        });

        $options = $grille
            ->reject(fn ($o) => isset($o['promo']))
            ->reject(fn ($o) => ($o['en_ligne'] ?? true) === false)
            ->reject(fn ($o, $n) => $dejaAbonne ? $n === 2 : ! empty($o['reabonnement']));

        $resultat = [];
        foreach ($options as $n => $o) {
            $promo = $actives->first(fn ($p) => in_array($n, $p['remplace'], true));
            $cle = $promo ? $actives->search($promo) : $n;
            $resultat[$cle] ??= $promo ?? $o;
        }

        return $resultat;
    }

    /**
     * Le createur a deja paye au moins une fois : ses offres sont des
     * renouvellements, ce que la page lui dit et ce qui fait basculer le
     * douze mois sur le tarif de reabonnement.
     */
    public function dejaAbonne(User $creatif): bool
    {
        return $creatif->invoices()->where('status', 'paid')->exists();
    }

    /** URL de la page de paiement Payplug. */
    public function commencer(User $creatif, int $option): string
    {
        $o = $this->options($creatif)[$option] ?? abort(404);

        $paiement = $this->payplug->creerPaiement([
            'amount' => (int) round($o['ttc'] * 100),
            'currency' => 'EUR',
            // Format de l'API actuelle (billing + shipping) ; le legacy
            // envoyait l'ancien objet `customer`.
            'billing' => $client = [
                'email' => $creatif->email,
                'first_name' => $creatif->firstname ?: '_',
                'last_name' => $creatif->lastname ?: '_',
                'address1' => $creatif->address ?: '-',
                'postcode' => $creatif->zipcode ?: '-',
                'city' => $creatif->city ?: '-',
                'country' => 'FR',
                'language' => app()->getLocale() === 'en' ? 'en' : 'fr',
            ],
            'shipping' => $client + ['delivery_type' => 'DIGITAL_GOODS'],
            'metadata' => [
                'customer_id' => $creatif->id,
                'product_id' => 1,
                'product_option' => $option,
            ],
            'hosted_payment' => [
                'return_url' => route('espace.formule.retour'),
                'cancel_url' => route('espace.formule'),
            ],
            'notification_url' => route('payplug.notification'),
        ]);

        return $paiement['url'];
    }

    /**
     * Traite une notification : prolonge la formule et emet la facture.
     * Rejouer une meme notification ne fait rien de plus.
     */
    public function traiter(string $corps): ?Invoice
    {
        $p = $this->payplug->lireNotification($corps);

        if (! $p || ! $p['paye']) {
            return null;
        }

        $option = config('formules.options.'.($p['metadata']['product_option'] ?? 0));
        $creatif = User::find($p['metadata']['customer_id'] ?? 0);

        // Le montant fait foi, pas les metadonnees : le legacy acceptait tout
        // paiement dont HT + TVA « tombait juste ».
        if (! $option || ! $creatif || $p['montant'] !== (int) round($option['ttc'] * 100)) {
            Log::warning('Payplug : paiement incoherent', ['id' => $p['id'], 'montant' => $p['montant'], 'metadata' => $p['metadata']]);

            return null;
        }

        // Un paiement de test ne prolonge jamais une formule en production.
        if (app()->isProduction() && ! ($p['brut']['is_live'] ?? false)) {
            Log::warning('Payplug : paiement de test recu en production', ['id' => $p['id']]);

            return null;
        }

        /*
         | Payplug rejoue ses notifications, parfois en meme temps. Le verrou
         | de ligne ne protege rien tant que la facture n'existe pas : on
         | serialise donc par paiement avant de chercher la facture.
         */
        return Cache::lock('payplug:'.$p['id'], 30)->block(10, fn () => DB::transaction(function () use ($p, $option, $creatif) {
            $existante = Invoice::where('gateway', 'payplug')->where('gateway_payload->id', $p['id'])->lockForUpdate()->first();

            if ($existante) {
                return $existante;
            }

            $creatif->prolongerFormule($option['mois']);

            $facture = $creatif->invoices()->create([
                'brand' => $creatif->brand ?: 'ub',
                'number' => 'tmp-'.$p['id'],
                'label' => $creatif->brand === 'df' ? 'Dustfolio' : 'Ultra-book',
                'designation' => $option['libelle'].' '.($creatif->brand === 'df' ? 'Dustfolio' : 'Ultra-book'),
                'amount' => $option['ttc'],
                'vat' => round($option['ttc'] - $option['ht'], 2),
                'currency' => 'EUR',
                'status' => 'paid',
                'gateway' => 'payplug',
                'gateway_payload' => $p['brut'],
                'issued_at' => now(),
                'paid_at' => now(),
            ]);
            $facture->update(['number' => $this->prochainNumero($creatif->brand ?: 'ub')]);

            return $facture;
        }));
    }

    /**
     * Numero suivant de la marque, a la suite des numeros du legacy.
     *
     * Pas l'id : les factures reprises gardent leur numero d'origine
     * (ub-10922 pour l'id 9230), et « ub-<id> » retombait sur un numero
     * deja pris (ub-9231). Le FOR UPDATE serialise deux paiements
     * simultanes de la meme marque, jusqu'a la fin de la transaction.
     */
    private function prochainNumero(string $marque): string
    {
        $dernier = (int) Invoice::where('number', 'like', $marque.'-%')
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING(number, ?) AS UNSIGNED)) AS dernier', [strlen($marque) + 2])
            ->value('dernier');

        return $marque.'-'.($dernier + 1);
    }
}
