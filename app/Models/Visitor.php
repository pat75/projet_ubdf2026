<?php

namespace App\Models;

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

    protected $fillable = ['email', 'firstname', 'lastname', 'password', 'brand', 'locale', 'signup_ip'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** « Prenom Nom », ou null si le visiteur ne les a pas donnes. */
    public function fullName(): ?string
    {
        $nom = trim($this->firstname.' '.$this->lastname);

        return $nom === '' ? null : $nom;
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

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\VisiteurMotDePasse($token));
    }
}
