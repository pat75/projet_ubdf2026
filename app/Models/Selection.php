<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Selection extends Model
{
    protected $fillable = ['legacy_id', 'brand', 'category_slug', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['sent_twitter', 'sent_instagram', 'sent_mail'])
            ->withTimestamps();
    }
}
