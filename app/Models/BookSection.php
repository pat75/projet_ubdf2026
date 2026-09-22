<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookSection extends Model
{
    protected $fillable = [
        'legacy_id', 'legacy_source', 'user_id', 'parent_id', 'kind', 'title', 'slug',
        'is_published', 'is_private', 'position', 'page_order', 'color', 'icon',
    ];

    /** Actualites du theme classique 2010 (bn_ultranews_*). */
    public const NEWS = 'news';

    /** Pages d'accueil des themes 2012+ (ub2_gal_rub, categorie 1). */
    public const ACCUEIL = 'accueil';

    /** Pages — bio, actualites — des themes 2012+ (categorie 3). */
    public const PAGES = 'pages';

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'is_private' => 'boolean', 'page_order' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(BookArticle::class);
    }
}
