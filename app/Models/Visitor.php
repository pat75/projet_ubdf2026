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

    /**
     * Initiales de l'avatar : prenom + nom ; un seul des deux → ses deux
     * premieres lettres ; aucun → les deux premiers caracteres de l'adresse.
     */
    public function initiales(): string
    {
        $prenom = trim((string) $this->firstname);
        $nom = trim((string) $this->lastname);

        $initiales = match (true) {
            $prenom !== '' && $nom !== '' => mb_substr($prenom, 0, 1).mb_substr($nom, 0, 1),
            $prenom !== '' || $nom !== '' => mb_substr($prenom.$nom, 0, 2),
            default => mb_substr((string) $this->email, 0, 2),
        };

        return mb_strtoupper($initiales);
    }

    /** Couleur stable de l'avatar, tiree de la palette des createurs. */
    public function couleur(): string
    {
        $palette = \App\Services\Espace\AffichageProfil::PALETTE;

        return $palette[crc32((string) $this->id) % count($palette)];
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
