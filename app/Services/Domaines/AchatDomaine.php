<?php

namespace App\Services\Domaines;

use App\Models\Domaine;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Achat d'un nom de domaine par un createur, depuis Ma formule :
 * recherche -> titulaire -> consentements -> verification OVH -> paiement
 * Payplug -> commande OVH (job). Voir _doc/_doc_VPS/03_achat_domaine.md.
 */
class AchatDomaine
{
    public function __construct(private readonly Ovh $ovh) {}

    /**
     * Disponibilite et prix du libelle dans les extensions demandees
     * (toutes les extensions proposees par defaut).
     *
     * @param  list<string>|null  $extensions
     * @return array<string, array{nom: string, disponible: bool, prix: float}>
     */
    public function rechercher(string $libelle, ?array $extensions = null): array
    {
        $resultats = [];
        $proposees = config('domaines.extensions');

        foreach ($extensions ? array_intersect_key($proposees, array_flip($extensions)) : $proposees as $extension => $prix) {
            $nom = $libelle.'.'.$extension;
            // Deja reserve chez nous (par un autre createur) : inutile d'interroger OVH.
            $libre = ! Domaine::where('nom', $nom)->whereIn('statut', Domaine::EN_COURS)->exists()
                && $this->ovh->disponibilite($nom)['disponible'];

            $resultats[$extension] = ['nom' => $nom, 'disponible' => $libre, 'prix' => (float) $prix];
        }

        return $resultats;
    }

    /** Libelle saisi -> forme attendue (« Jeanne Dupont.fr » -> « jeanne-dupont »). */
    public function libelle(string $saisie): string
    {
        $saisie = preg_replace('/\.(fr|com)$/i', '', trim(Str::lower($saisie)));

        return trim(preg_replace('/[^a-z0-9-]+/', '-', Str::ascii($saisie)), '-');
    }

    /**
     * Titulaire pre-rempli depuis Mon compte, aux formats attendus par OVH.
     *
     * @return array<string, string>
     */
    public function titulaireDepuis(User $creatif): array
    {
        $pays = $this->codePays((string) $creatif->country) ?? 'FR';

        return [
            'prenom' => (string) $creatif->firstname,
            'nom' => (string) $creatif->lastname,
            'societe' => (string) $creatif->company,
            'email' => (string) $creatif->email,
            'telephone' => $this->telephone((string) ($creatif->mobile ?: $creatif->phone), $pays),
            'adresse' => (string) $creatif->address,
            'code_postal' => (string) $creatif->zipcode,
            'ville' => (string) $creatif->city,
            'pays' => $pays,
        ];
    }

    /** @return array<string, mixed> regles de validation du titulaire */
    public function reglesTitulaire(string $extension): array
    {
        $pays = $extension === 'fr' ? config('domaines.ue') : array_keys(config('domaines.pays'));

        return [
            'titulaire.prenom' => ['required', 'string', 'max:100'],
            'titulaire.nom' => ['required', 'string', 'max:100'],
            'titulaire.societe' => ['nullable', 'string', 'max:150'],
            'titulaire.email' => ['required', 'email', 'max:255'],
            'titulaire.telephone' => ['required', 'string', 'regex:/^\+\d{1,3}\.\d{4,14}$/'],
            'titulaire.adresse' => ['required', 'string', 'max:255'],
            'titulaire.code_postal' => ['required', 'string', 'max:20'],
            'titulaire.ville' => ['required', 'string', 'max:100'],
            'titulaire.pays' => ['required', 'in:'.implode(',', $pays)],
        ];
    }

    /**
     * Juste avant le paiement : OVH confirme que le domaine est toujours
     * libre (validation a blanc du panier), puis le domaine est enregistre
     * « a payer » avec le titulaire et les preuves de consentement.
     */
    public function preparer(User $creatif, string $nom, array $titulaire, string $ip): Domaine
    {
        $nom = Domaine::normaliser($nom);
        $extension = substr(strrchr($nom, '.'), 1);
        $prix = Domaine::prix($extension) ?? throw new RuntimeException(__('Extension non proposée.'));

        if (Domaine::where('nom', $nom)->whereIn('statut', Domaine::EN_COURS)->exists()) {
            throw new RuntimeException(__('Ce nom de domaine n’est plus disponible.'));
        }

        try {
            $prixOvh = $this->ovh->verifierCommande($nom);
        } catch (RuntimeException $e) {
            report($e);
            $prixOvh = null;
        }

        if ($prixOvh === null) {
            throw new RuntimeException(__('Ce nom de domaine n’est plus disponible.'));
        }

        // Une demande abandonnee (paiement jamais termine) ne bloque pas le nom.
        Domaine::where('user_id', $creatif->id)->where('statut', 'a_payer')->delete();

        return Domaine::create([
            'user_id' => $creatif->id,
            'nom' => $nom,
            'extension' => $extension,
            'prix_ttc' => $prix,
            'statut' => 'a_payer',
            'source' => 'ovh',
            'titulaire' => $titulaire,
            'cgu_acceptees_le' => now(),
            'renonciation_le' => now(),
            'consentement_ip' => $ip,
            'consentement_version' => config('domaines.version_consentement'),
        ]);
    }

    private function codePays(string $saisie): ?string
    {
        $saisie = Str::lower(Str::ascii(trim($saisie)));

        foreach (config('domaines.pays') as $code => $libelle) {
            if ($saisie === Str::lower($code) || $saisie === Str::lower(Str::ascii($libelle))) {
                return $code;
            }
        }

        return null;
    }

    /** « 06 12 34 56 78 », « 33612345678 » ou « +33612345678 » -> « +33.612345678 ». */
    public function telephone(string $saisie, string $pays = 'FR'): string
    {
        $chiffres = preg_replace('/\D/', '', $saisie);

        if ($chiffres === '') {
            return '';
        }

        if (str_starts_with($chiffres, '33') && strlen($chiffres) === 11) {
            return '+33.'.substr($chiffres, 2);
        }

        if ($pays === 'FR' && str_starts_with($chiffres, '0') && strlen($chiffres) === 10) {
            return '+33.'.substr($chiffres, 1);
        }

        // Autre pays : laisse au createur le soin de completer l'indicatif.
        return str_starts_with(trim($saisie), '+') ? '+'.substr($chiffres, 0, 2).'.'.substr($chiffres, 2) : $saisie;
    }
}
