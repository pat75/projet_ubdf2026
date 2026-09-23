<?php

namespace App\Services\Facturation;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Interrogation de l'annuaire des entreprises de l'Etat.
 *
 * `recherche-entreprises.api.gouv.fr` est ouverte : ni compte ni cle, sept
 * appels par seconde. Elle donne, pour un SIRET, la raison sociale,
 * l'adresse de l'etablissement, son code d'activite, sa forme juridique
 * et son numero de TVA intracommunautaire — tout ce que reclame une
 * facture electronique.
 *
 * Les reponses sont gardees un mois : une entreprise ne change pas de
 * raison sociale toutes les semaines, et cela evite de repartir en ligne
 * a chaque affichage de la fiche.
 */
class AnnuaireEntreprises
{
    public function __construct(private readonly int $delai = 8) {}

    /**
     * Cherche un etablissement par son SIRET.
     *
     * @return array<string, mixed>
     *
     * @throws SiretIntrouvable si l'annuaire ne connait pas ce numero
     * @throws RuntimeException si l'annuaire ne repond pas
     */
    public function parSiret(string $siret): array
    {
        $siret = $this->normaliser($siret);

        if (! $this->siretValide($siret)) {
            throw new SiretInvalide(__('Ce numéro SIRET n’est pas valide : il compte 14 chiffres et sa clé de contrôle doit tomber juste.'));
        }

        return Cache::remember('siret:'.$siret, now()->addMonth(), function () use ($siret) {
            $reponse = $this->appeler($siret);

            $entreprise = $reponse['results'][0] ?? null;

            if ($entreprise === null) {
                throw new SiretIntrouvable(__('Aucune entreprise ne correspond à ce SIRET dans l’annuaire de l’État.'));
            }

            return $this->extraire($siret, $entreprise);
        });
    }

    /** @return array<string, mixed> */
    private function appeler(string $siret): array
    {
        try {
            $reponse = Http::timeout($this->delai)
                // `throw: false` : on veut la reponse en main pour rendre
                // un message au createur, pas une exception de transport.
                ->retry(2, 200, throw: false)
                ->acceptJson()
                ->get(rtrim(config('facturation.annuaire.url'), '/').'/search', [
                    'q' => $siret,
                    'per_page' => 1,
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Annuaire des entreprises injoignable', ['siret' => $siret, 'erreur' => $e->getMessage()]);

            throw new RuntimeException(__('L’annuaire des entreprises ne répond pas. Réessayez dans un instant.'));
        }

        if ($reponse->failed()) {
            Log::warning('Annuaire des entreprises en erreur', ['siret' => $siret, 'statut' => $reponse->status()]);

            throw new RuntimeException(__('L’annuaire des entreprises ne répond pas. Réessayez dans un instant.'));
        }

        return $reponse->json() ?? [];
    }

    /**
     * Ramene la reponse aux seuls champs qui nous servent.
     *
     * L'API rend l'entreprise a la racine et l'etablissement cherche dans
     * `matching_etablissements`. Quand le SIRET est celui du siege, elle
     * ne remplit pas toujours ce tableau : on retombe alors sur `siege`.
     *
     * @param  array<string, mixed>  $entreprise
     * @return array<string, mixed>
     */
    private function extraire(string $siret, array $entreprise): array
    {
        $etablissement = collect($entreprise['matching_etablissements'] ?? [])
            ->firstWhere('siret', $siret)
            ?? $entreprise['siege']
            ?? [];

        return [
            'siret' => $siret,
            'siren' => (string) ($entreprise['siren'] ?? substr($siret, 0, 9)),
            'company_name' => (string) ($entreprise['nom_complet'] ?? $entreprise['nom_raison_sociale'] ?? ''),
            'legal_form' => $entreprise['nature_juridique'] ?? null,
            'naf_code' => $etablissement['activite_principale'] ?? $entreprise['activite_principale'] ?? null,
            'vat_number' => $entreprise['tva'][0] ?? $this->tvaIntracommunautaire((string) ($entreprise['siren'] ?? substr($siret, 0, 9))),
            'address' => $etablissement['adresse'] ?? null,
            'postcode' => $etablissement['code_postal'] ?? null,
            'city' => $etablissement['libelle_commune'] ?? null,
            'country' => 'FR',
            'admin_state' => $etablissement['etat_administratif'] ?? $entreprise['etat_administratif'] ?? null,
            'established_on' => $etablissement['date_creation'] ?? $entreprise['date_creation'] ?? null,
            'payload' => $entreprise,
        ];
    }

    /** Ne garde que les chiffres : les SIRET se dictent par groupes. */
    public function normaliser(string $siret): string
    {
        return preg_replace('/\D+/', '', $siret) ?? '';
    }

    /**
     * Cle de Luhn, comme sur une carte bancaire : elle attrape les fautes
     * de frappe avant d'aller deranger l'annuaire.
     */
    public function siretValide(string $siret): bool
    {
        if (strlen($siret) !== 14) {
            return false;
        }

        $somme = 0;

        foreach (str_split(strrev($siret)) as $rang => $chiffre) {
            $valeur = (int) $chiffre;

            if ($rang % 2 === 1) {
                $valeur *= 2;
                $valeur = $valeur > 9 ? $valeur - 9 : $valeur;
            }

            $somme += $valeur;
        }

        return $somme % 10 === 0;
    }

    /**
     * Numero de TVA francais, quand l'annuaire ne le donne pas : FR, puis
     * une cle sur deux chiffres, puis le SIREN.
     */
    public function tvaIntracommunautaire(string $siren): ?string
    {
        if (strlen($siren) !== 9 || ! ctype_digit($siren)) {
            return null;
        }

        $cle = (12 + 3 * ((int) $siren % 97)) % 97;

        return sprintf('FR%02d%s', $cle, $siren);
    }
}
