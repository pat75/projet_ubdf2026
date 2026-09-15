<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookSection extends Model
{
    protected $fillable = [
        'legacy_id', 'user_id', 'parent_id', 'title', 'slug',
        'is_published', 'is_private', 'position', 'color', 'icon',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'is_private' => 'boolean'];
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
