<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Compte visiteur : cree depuis le coeur du memo book, pour retrouver sa
 * selection d'un appareil a l'autre. Guard `visitor`, sans acces a
 * l'espace creatif.
 */
class Visitor extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['email', 'password', 'brand', 'locale', 'signup_ip'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function memoBooks(): HasMany
    {
        return $this->hasMany(MemoBook::class);
    }

    public function visites(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'visitor_book_visits', 'visitor_id', 'book_id')
            ->withPivot('visited_at')
            ->orderByPivot('visited_at', 'desc');
    }

    /**
     * Demandes envoyees depuis le portail avec son adresse.
     *
     * Seulement une fois l'adresse confirmee : sans cela, n'importe qui
     * ouvrirait un compte au nom d'un tiers pour lire ses echanges.
     */
    public function demandes(): Builder
    {
        return Conversation::query()
            ->where('sender_email', $this->email_verified_at ? $this->email : '')
            ->where('is_spam', false);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\VisiteurMotDePasse($token));
    }
}
