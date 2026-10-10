<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use SoftDeletes;

    /** Liste des fichiers proteges du book, gardee en cache : voir AccesPortfolios. */
    protected static function booted(): void
    {
        $oublier = fn (self $modele) => \App\Services\Book\AccesPortfolios::oublier($modele->user_id);
        static::saved($oublier);
        static::deleted($oublier);
    }

    protected $hidden = ['password'];

    protected $fillable = [
        'legacy_id', 'user_id', 'parent_id', 'name', 'slug',
        'status', 'position', 'color', 'media_order', 'password',
    ];

    protected function casts(): array
    {
        return ['media_order' => 'array', 'password' => 'encrypted'];
    }

    /** Protege par un mot de passe demande aux visiteurs du book. */
    public function estProtegee(): bool
    {
        return filled($this->password);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
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

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }
}
