<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Une actualite affichee dans l'espace (table `actualites`).
 *
 * `emplacement` dit ou elle paraît : `creatif`, `visiteur`, `deux`
 * (les deux tableaux de bord) ou `tous`.
 */
class Actualite extends Model
{
    protected $fillable = [
        'titre',
        'sous_titre',
        'contenu',
        'images',
        'emplacement',
        'ordre',
        'actif',
    ];

    protected $casts = [
        'images' => 'array',
        'actif' => 'boolean',
        'ordre' => 'integer',
    ];

    /** Les emplacements proposes au back-office. */
    public const EMPLACEMENTS = [
        'creatif' => 'Espace créatif',
        'visiteur' => 'Compte visiteur',
        'deux' => 'Créatif et visiteur',
        'tous' => 'Partout',
    ];

    public function scopeActif(Builder $requete): Builder
    {
        return $requete->where('actif', true);
    }

    /** Celles qui paraissent sur `$emplacement` (`creatif` ou `visiteur`). */
    public function scopePour(Builder $requete, string $emplacement): Builder
    {
        return $requete->whereIn('emplacement', [$emplacement, 'deux', 'tous']);
    }

    public function scopeOrdonne(Builder $requete): Builder
    {
        return $requete->orderBy('ordre')->orderByDesc('created_at');
    }

    /** Le premier visuel, s'il y en a un. */
    public function visuel(): ?string
    {
        $images = $this->images ?: [];

        return $images ? (string) reset($images) : null;
    }
}
