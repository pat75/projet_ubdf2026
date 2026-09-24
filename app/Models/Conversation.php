<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'legacy_id', 'user_id', 'channel', 'subject', 'request_detail',
        'sender_name', 'sender_company', 'sender_email', 'sender_phone',
        'legacy_token', 'selector', 'owner_token', 'sender_token',
        'book_image', 'is_spam', 'last_message_at',
    ];

    /** Les jetons haches ne sortent jamais du modele. */
    protected $hidden = ['legacy_token', 'owner_token', 'sender_token'];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime', 'is_spam' => 'boolean', 'spam_ia' => 'boolean', 'spam_ia_probabilite' => 'float'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /** Intitule lisible de la demande, tel qu'affiche dans le fil. */
    public function objet(): string
    {
        return config('messagerie.demandes.'.$this->subject.'.libelle', $this->subject ?? '');
    }
}
