<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Nom de domaine personnel d'un creatif, qui affiche son book.
 *
 * Statuts : en_attente (commande ou DNS pas encore pointe), dns_ok (le
 * DNS pointe vers le VPS : on laisse Caddy demander le certificat),
 * actif (servi), suspendu (coupe par l'administration ou la formule),
 * expire (non renouvele).
 */
class Domaine extends Model
{
    /** Statuts pour lesquels le VPS peut demander un certificat et servir le book. */
    public const SERVIS = ['dns_ok', 'actif'];

    protected $table = 'domaines';

    protected $fillable = ['user_id', 'nom', 'statut', 'source', 'ovh_commande_id', 'expire_le', 'verifie_le'];

    protected function casts(): array
    {
        return ['expire_le' => 'date', 'verifie_le' => 'datetime'];
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
