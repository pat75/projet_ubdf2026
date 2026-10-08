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

    /**
     * Mots-cles les plus frequents des visuels publics d'un createur, dans la
     * langue courante : ses specialites (bloc « a propos » du book, llms.txt).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function principauxDe(int $userId, int $nombre = 6): \Illuminate\Database\Eloquent\Collection
    {
        return self::query()
            ->select('tags.id', 'tags.label', 'tags.slug')
            ->join('media_tag', 'media_tag.tag_id', '=', 'tags.id')
            ->whereIn('media_tag.media_id', Media::query()
                ->where('user_id', $userId)->published()->horsProteges()->select('media.id'))
            ->where('tags.lang', self::langueCourante())
            ->groupBy('tags.id', 'tags.label', 'tags.slug')
            ->orderByRaw('COUNT(*) DESC')
            ->limit($nombre)
            ->get();
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
