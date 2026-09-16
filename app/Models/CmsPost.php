<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Actualite du portail (`post` de l'ancien WordPress). */
class CmsPost extends Model
{
    protected $fillable = [
        'legacy_id', 'slug', 'locale', 'title', 'body', 'excerpt',
        'image', 'published_at',
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
}
