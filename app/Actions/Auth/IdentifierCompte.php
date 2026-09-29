<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Facades\Hash;

/**
 * Retrouve le compte qui se connecte : un creatif par son identifiant,
 * un creatif ou un visiteur par son adresse mail.
 *
 * Une adresse n'est pas unique chez les creatifs (plusieurs books sur la
 * meme adresse). Le mot de passe departage : seuls les comptes dont il
 * est le bon sont retenus. S'il en reste plusieurs, la connexion par
 * adresse est refusee et l'identifiant demande.
 */
class IdentifierCompte
{
    public const AMBIGU = 'ambigu';

    /** @return User|Visitor|self::AMBIGU|null */
    public function executer(string $identifiant, string $motDePasse): User|Visitor|string|null
    {
        $identifiant = mb_strtolower(trim($identifiant));

        if (! str_contains($identifiant, '@')) {
            $creatif = User::query()->where('login', $identifiant)->first();

            return $creatif && Hash::check($motDePasse, $creatif->password) ? $creatif : null;
        }

        $comptes = User::query()->where('email', $identifiant)->get()
            ->push(Visitor::query()->where('email', $identifiant)->first())
            ->filter(fn ($compte) => $compte && Hash::check($motDePasse, $compte->password))
            ->values();

        return match ($comptes->count()) {
            0 => null,
            1 => $comptes->first(),
            default => self::AMBIGU,
        };
    }
}
