<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingOffer extends Model
{
    public const PROMO_6_MOIS = 'promo-auto-6mois';

    protected $fillable = ['legacy_id', 'user_id', 'type', 'offered_at'];

    protected function casts(): array
    {
        return ['offered_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
