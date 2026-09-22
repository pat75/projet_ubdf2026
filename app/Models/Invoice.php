<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'legacy_id', 'legacy_source', 'user_id', 'brand', 'number', 'label',
        'designation', 'amount', 'vat', 'currency', 'status', 'gateway',
        'gateway_payload', 'issued_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'gateway_payload' => 'array',
            'amount' => 'decimal:2',
            'vat' => 'decimal:2',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /** Numero affiche, au format du legacy : UB-2020-7907. */
    public function numero(): string
    {
        return strtoupper($this->brand ?: 'ub').'-'.$this->issued_at?->format('Y').'-'.($this->legacy_id ?? $this->id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
