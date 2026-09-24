<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookArticle extends Model
{
    protected $fillable = [
        'legacy_id', 'legacy_source', 'user_id', 'book_section_id', 'title', 'slug', 'body', 'body_blocks',
        'image', 'keywords', 'status', 'position', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'body_blocks' => 'array'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(BookSection::class, 'book_section_id');
    }
}
