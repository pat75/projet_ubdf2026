<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** Mot-cle attribue a un visuel par l'analyse IA. */
class Tag extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['label', 'slug', 'lang'];

    /** Seuils d'indexation d'une page /images/{slug} : assez d'images, de plusieurs createurs. */
    public const INDEXABLE_IMAGES = 8;

    public const INDEXABLE_CREATIFS = 3;

    protected static function booted(): void
    {
        static::creating(fn (self $tag) => $tag->slug ??= \Illuminate\Support\Str::slug($tag->label));
    }

    /** Langue des mots-cles montres dans la langue courante. */
    public static function langueCourante(): string
    {
        return app()->getLocale() === 'en' ? 'en' : 'fr';
    }

    public function url(): string
    {
        return lien('images.motcle', ['slug' => $this->slug]);
    }

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
