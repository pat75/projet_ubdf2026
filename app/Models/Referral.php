<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'legacy_id', 'sponsor_id', 'referred_id', 'referred_email', 'status', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return ['confirmed_at' => 'datetime'];
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
