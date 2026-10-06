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

    /**
     * Compte reconnu, mais ferme au back-office. Distingue de l'echec
     * d'identification : le mot de passe etait bon, dire « incorrect »
     * enverrait la personne le reinitialiser en boucle.
     */
    public const BLOQUE = 'bloque';

    /** @return User|Visitor|self::AMBIGU|self::BLOQUE|null */
    public function executer(string $identifiant, string $motDePasse): User|Visitor|string|null
    {
        $compte = $this->reconnaitre($identifiant, $motDePasse);

        if (! $compte instanceof User && ! $compte instanceof Visitor) {
            return $compte;
        }

        $this->remettreAuCout($compte, $motDePasse);

        return $compte->estBloque() ? self::BLOQUE : $compte;
    }

    /**
     * La reprise legacy hache a un cout reduit pour convertir vite
     * (LegacyMigrator::password()) : le mot de passe, connu a cet instant,
     * est rehache au cout de la config.
     */
    private function remettreAuCout(User|Visitor $compte, string $motDePasse): void
    {
        if (Hash::needsRehash($compte->password)) {
            $compte->forceFill(['password' => Hash::make($motDePasse)])->saveQuietly();
        }
    }

    /** @return User|Visitor|self::AMBIGU|null */
    private function reconnaitre(string $identifiant, string $motDePasse): User|Visitor|string|null
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
