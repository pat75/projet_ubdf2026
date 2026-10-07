<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** Mot-cle attribue a un visuel par l'analyse IA. */
class Tag extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['label', 'lang'];

    /** Forme stockee : minuscules, espaces resserres. */
    public static function normaliser(string $label): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', mb_strtolower($label))), 0, 80);
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class);
    }
}
