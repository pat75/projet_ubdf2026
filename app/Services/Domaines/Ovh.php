<?php

namespace App\Services\Domaines;

use App\Models\Domaine;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Achat de noms de domaine et pointage DNS par l'API OVH.
 *
 * Client HTTP de Laravel plutot que le paquet ovh/ovh : la signature tient
 * en quelques lignes, et Http::fake() suffit aux tests.
 *
 * Le creatif est titulaire (contact « owner ») ; le compte OVH de la
 * plateforme reste contact technique et administratif.
 *
 * ponytail: enchainement de commande d'apres la documentation publique
 * (panier /order/cart) — a valider sur un premier achat reel en console.
 */
class Ovh
{
    /** @return array{disponible: bool, prix: ?string} prix TTC affiche par OVH (« 8.39 € ») */
    public function disponibilite(string $nom): array
    {
        $panier = $this->panier();
        $offres = $this->appeler('GET', "/order/cart/{$panier}/domain", query: ['domain' => Domaine::normaliser($nom)])->json();
        $offre = collect($offres)->firstWhere('action', 'create');

        return [
            'disponible' => (bool) ($offre['orderable'] ?? false),
            'prix' => collect($offre['prices'] ?? [])->firstWhere('label', 'TOTAL')['price']['text'] ?? null,
        ];
    }

    /**
     * Verification juste avant le paiement du createur : le domaine est
     * toujours libre et le panier OVH passerait (validation a blanc, sans
     * bon de commande). Rend le prix TTC facture par OVH, pour controle.
     */
    public function verifierCommande(string $nom): ?float
    {
        $panier = $this->panier();
        $this->appeler('POST', "/order/cart/{$panier}/domain", ['domain' => Domaine::normaliser($nom)]);
        $total = $this->appeler('GET', "/order/cart/{$panier}/checkout")->json('prices.withTax.value');

        return $total === null ? null : (float) $total;
    }

    /**
     * Code de transfert (AuthCode) a remettre au createur, verrou leve.
     * ponytail: routes d'apres la console API (/domain/{nom}), a valider en reel.
     */
    public function authCode(Domaine $domaine): string
    {
        $this->appeler('PUT', "/domain/{$domaine->nom}", ['transferLockStatus' => 'unlocked']);

        return (string) $this->appeler('GET', "/domain/{$domaine->nom}/authInfo")->json();
    }

    /** Pas de renouvellement : le domaine reste actif jusqu'a son echeance puis est supprime. */
    public function resilier(Domaine $domaine): void
    {
        $this->appeler('PUT', "/domain/{$domaine->nom}/serviceInfos", [
            'renew' => ['automatic' => false, 'deleteAtExpiration' => true, 'forced' => false, 'period' => null],
        ]);
    }

    /**
     * Commande le domaine au nom du creatif et rend l'identifiant de commande.
     * Paye avec le moyen de paiement par defaut du compte OVH.
     *
     * @param  array{prenom: string, nom: string, email: string, telephone: string, adresse: string, code_postal: string, ville: string, pays: string, societe?: ?string}  $titulaire
     */
    public function commander(Domaine $domaine, array $titulaire): string
    {
        $contact = $this->appeler('POST', '/me/contact', [
            'firstName' => $titulaire['prenom'],
            'lastName' => $titulaire['nom'],
            'email' => $titulaire['email'],
            'phone' => $titulaire['telephone'],
            'organisationName' => $titulaire['societe'] ?? null,
            'legalForm' => empty($titulaire['societe']) ? 'individual' : 'corporation',
            'language' => 'fr_FR',
            'address' => [
                'line1' => $titulaire['adresse'],
                'zip' => $titulaire['code_postal'],
                'city' => $titulaire['ville'],
                'country' => strtoupper($titulaire['pays']),
            ],
        ])->json('id');

        $panier = $this->panier();
        $article = $this->appeler('POST', "/order/cart/{$panier}/domain", ['domain' => $domaine->nom])->json('itemId');
        $this->appeler('POST', "/order/cart/{$panier}/item/{$article}/configuration", [
            'label' => 'OWNER_CONTACT',
            'value' => "/me/contact/{$contact}",
        ]);

        // dry-run : validation a blanc du panier complet, jamais de bon de commande.
        $commande = config('domaines.ovh_mode') === 'reel'
            ? (string) $this->appeler('POST', "/order/cart/{$panier}/checkout", [
                'autoPayWithPreferredPaymentMethod' => true,
                'waiveRetractationPeriod' => true,
            ])->json('orderId')
            : 'dry-run-'.$this->appeler('GET', "/order/cart/{$panier}/checkout")->json('orderId', $panier);

        $domaine->update(['source' => 'ovh', 'ovh_commande_id' => $commande, 'statut' => 'en_attente']);

        return $commande;
    }

    /** Fait pointer le domaine (et www) vers le VPS. Le domaine doit etre dans le compte OVH. */
    public function pointerVersVps(Domaine $domaine): void
    {
        $ip = config('services.ovh.ip_vps') ?: throw new RuntimeException('VPS_IP non configure.');

        foreach (['', 'www'] as $sousDomaine) {
            $this->appeler('POST', "/domain/zone/{$domaine->nom}/record", ['fieldType' => 'A', 'subDomain' => $sousDomaine, 'target' => $ip, 'ttl' => 3600]);

            if ($ip6 = config('services.ovh.ip6_vps')) {
                $this->appeler('POST', "/domain/zone/{$domaine->nom}/record", ['fieldType' => 'AAAA', 'subDomain' => $sousDomaine, 'target' => $ip6, 'ttl' => 3600]);
            }
        }

        $this->appeler('POST', "/domain/zone/{$domaine->nom}/refresh");
    }

    private function panier(): string
    {
        $panier = $this->appeler('POST', '/order/cart', ['ovhSubsidiary' => config('services.ovh.filiale')])->json('cartId');
        $this->appeler('POST', "/order/cart/{$panier}/assign");

        return $panier;
    }

    /**
     * Requete signee : $1$ + SHA1(secret+consumer+methode+url+corps+horodatage).
     *
     * @param  array<string, mixed>|null  $corps
     * @param  array<string, string>  $query
     */
    private function appeler(string $methode, string $chemin, ?array $corps = null, array $query = []): Response
    {
        $config = config('services.ovh');

        if (! $config['application_key'] || ! $config['application_secret'] || ! $config['consumer_key']) {
            throw new RuntimeException('Cles OVH non configurees.');
        }

        $url = rtrim($config['endpoint'], '/').$chemin.($query ? '?'.http_build_query($query) : '');
        $json = $corps === null ? '' : json_encode($corps, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        // ponytail: horloge locale ; si OVH refuse l'horodatage, caler sur GET /auth/time.
        $horodatage = (string) time();
        $signature = '$1$'.sha1(implode('+', [$config['application_secret'], $config['consumer_key'], $methode, $url, $json, $horodatage]));

        $reponse = Http::withHeaders([
            'X-Ovh-Application' => $config['application_key'],
            'X-Ovh-Consumer' => $config['consumer_key'],
            'X-Ovh-Timestamp' => $horodatage,
            'X-Ovh-Signature' => $signature,
        ])->withBody($json, 'application/json')->send($methode, $url);

        if ($reponse->failed()) {
            throw new RuntimeException('OVH '.$methode.' '.$chemin.' : '.($reponse->json('message') ?? $reponse->status()));
        }

        return $reponse;
    }
}
