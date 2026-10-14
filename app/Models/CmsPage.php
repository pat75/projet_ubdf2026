<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = [
        'legacy_id', 'translation_group', 'slug', 'locale', 'marque', 'parent_slug',
        'title', 'seo_title', 'body', 'excerpt', 'position', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePubliees(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Pages servies sous une marque : les siennes, plus les pages communes
     * qu'aucune des siennes ne remplace (meme slug, meme langue).
     */
    public function scopePourMarque(Builder $query, string $marque): void
    {
        $query->where(fn (Builder $q) => $q->where('cms_pages.marque', $marque)
            ->orWhere(fn (Builder $q) => $q->where('cms_pages.marque', '')
                ->whereNotExists(fn ($sous) => $sous->from('cms_pages as propre')
                    ->whereColumn('propre.slug', 'cms_pages.slug')
                    ->whereColumn('propre.locale', 'cms_pages.locale')
                    ->where('propre.marque', $marque))));
    }

    /** Copie d'une page commune, servie sous une autre marque (noindex). */
    public function estUneCopiePour(\App\Support\Marque $marque): bool
    {
        return $this->marque !== $marque->code && $marque->reprendLeContenuDe($this->locale);
    }

    /** Page quasi vide : servie, mais pas indexee (moins de 80 mots). */
    public function estMince(): bool
    {
        return str_word_count(strip_tags($this->body ?? '')) < 80;
    }

    /** La meme page dans les autres langues, via le groupe de traduction. */
    public function traductions(): Builder
    {
        return static::query()
            ->publiees()
            ->where('translation_group', $this->translation_group)
            ->whereKeyNot($this->getKey());
    }
}
