<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'legacy_id', 'brand', 'type', 'name', 'subject', 'body', 'scheduled_at', 'sent_at',
    ];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'sent_at' => 'datetime'];
    }

    public function sends(): HasMany
    {
        return $this->hasMany(CampaignSend::class);
    }
}
