<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Nom de domaine personnel d'un creatif, qui affiche son book.
 *
 * Statuts :
 *  - achat : a_payer (OVH verifie, paiement Payplug en cours), paye,
 *    commande (passee chez OVH), echec_commande, rembourse ;
 *  - service : en_attente (DNS pas encore pointe), dns_ok (DNS vers le
 *    VPS : Caddy peut demander le certificat), actif (servi), suspendu,
 *    expire, resilie (ne sera pas renouvele), transfert (code remis).
 */
class Domaine extends Model
{
    /** Statuts pour lesquels le VPS peut demander un certificat et servir le book. */
    public const SERVIS = ['dns_ok', 'actif'];

    protected $table = 'domaines';

    /** Relances avant l'echeance, en jours (J-30, J-15, J-5). */
    public const RELANCES = [30, 15, 5];

    /** Statuts d'un domaine que le createur possede (ou va posseder) : un seul a la fois. */
    public const EN_COURS = ['a_payer', 'paye', 'commande', 'en_attente', 'dns_ok', 'actif', 'suspendu', 'resilie', 'transfert'];

    protected $fillable = [
        'user_id', 'nom', 'extension', 'prix_ttc', 'statut', 'source', 'titulaire',
        'cgu_acceptees_le', 'renonciation_le', 'consentement_ip', 'consentement_version',
        'payplug_id', 'invoice_id', 'ovh_commande_id', 'reserve_le', 'resilie_le', 'expire_le', 'verifie_le',
        'relances_envoyees', 'derniere_relance_le',
    ];

    protected function casts(): array
    {
        return [
            'prix_ttc' => 'decimal:2',
            // Donnees personnelles, transmises au registre : chiffrees en base.
            'titulaire' => 'encrypted:array',
            'cgu_acceptees_le' => 'datetime',
            'renonciation_le' => 'datetime',
            'reserve_le' => 'datetime',
            'resilie_le' => 'datetime',
            'expire_le' => 'date',
            'verifie_le' => 'datetime',
            'derniere_relance_le' => 'datetime',
        ];
    }

    /**
     * Achat ouvert ? Reserve au developpement tant que la facturation des
     * domaines n'est pas validee (filet rouge dev_only dans l'interface).
     */
    public static function achatOuvert(): bool
    {
        return app()->environment(['local', 'development', 'testing']);
    }

    /** Prix TTC d'une extension proposee, null si elle ne l'est pas. */
    public static function prix(string $extension): ?float
    {
        $prix = config('domaines.extensions.'.$extension);

        return $prix === null ? null : (float) $prix;
    }

    /**
     * Libelle seul (sans extension) acceptable chez les registres :
     * a-z, 0-9 et tiret, 1 a 63 caracteres, pas de tiret en bord. Pas
     * d'accents pour l'instant (noms internationalises).
     */
    public static function libelleValide(string $libelle): bool
    {
        return (bool) preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)$/', $libelle) && ! str_contains(substr($libelle, 2, 2), '--');
    }

    /** Toujours en minuscules, sans « www. » ni point final : c'est la cle de recherche. */
    public static function normaliser(string $nom): string
    {
        return preg_replace('/^www\./', '', rtrim(strtolower(trim($nom)), '.'));
    }

    public function scopeServis(Builder $requete): Builder
    {
        return $requete->whereIn('statut', self::SERVIS);
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
