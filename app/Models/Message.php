<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'legacy_id', 'conversation_id', 'from_owner', 'body', 'ip', 'read_at',
    ];

    protected function casts(): array
    {
        return ['from_owner' => 'boolean', 'read_at' => 'datetime'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
