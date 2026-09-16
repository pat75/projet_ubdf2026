<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = [
        'legacy_id', 'translation_group', 'slug', 'locale', 'parent_slug',
        'title', 'body', 'excerpt', 'position', 'published_at',
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

    /** La meme page dans les autres langues, via le groupe de traduction. */
    public function traductions(): Builder
    {
        return static::query()
            ->publiees()
            ->where('translation_group', $this->translation_group)
            ->whereKeyNot($this->getKey());
    }
}
