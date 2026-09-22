<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    /** Code du legacy : credite `discount` mois de formule. */
    public const MOIS = 'months';

    protected $fillable = [
        'legacy_id', 'code', 'label', 'discount', 'discount_type',
        'max_uses', 'uses', 'starts_at', 'ends_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
