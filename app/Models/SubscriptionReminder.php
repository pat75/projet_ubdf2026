<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionReminder extends Model
{
    protected $fillable = ['user_id', 'expires_on', 'days_before', 'sent_at', 'sent_on'];

    protected function casts(): array
    {
        return ['expires_on' => 'date', 'sent_at' => 'datetime', 'sent_on' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
