<?php

namespace App\Services\Auth;

use App\Mail\MotDePasseOublie;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Recuperation de mot de passe.
 *
 * Le legacy renvoyait le mot de passe **en clair dans le corps du mail**
 * (« Mot de passe: %us_pass% », front/ajax_2010.php). Il le pouvait parce
 * qu'il chiffrait les mots de passe de facon reversible. L'import les a
 * rehashes en bcrypt : ils ne sont plus lisibles, et c'est tant mieux. La
 * recuperation devient donc une **reinitialisation** par lien a usage
 * unique.
 *
 * Un meme creatif pouvant avoir plusieurs comptes sur la meme adresse, le
 * mail porte un lien par compte — ce que le legacy avait aussi, sous la
 * forme d'une liste de logins.
 */
class MotDePasse
{
    /** Duree de validite d'un lien de reinitialisation. */
    public const VALIDITE_HEURES = 2;

    /**
     * Envoie un lien de reinitialisation pour chaque compte portant cette
     * adresse. Retourne le nombre de comptes trouves.
     */
    public function demander(string $email, ?string $ip = null): int
    {
        $comptes = User::query()->where('email', $email)->get();

        if ($comptes->isEmpty()) {
            return 0;
        }

        $liens = $comptes->map(fn (User $compte) => [
            'login' => $compte->login,
            'lien' => $this->creerLien($compte, $ip),
        ])->all();

        Mail::to($email)->send(new MotDePasseOublie($liens));

        return $comptes->count();
    }

    /** Cree un jeton et retourne l'URL de reinitialisation correspondante. */
    public function creerLien(User $compte, ?string $ip = null): string
    {
        // Les demandes precedentes encore vivantes sont invalidees : un seul
        // lien actif a la fois par compte.
        PasswordReset::query()
            ->where('user_id', $compte->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $jeton = bin2hex(random_bytes(32));

        $demande = PasswordReset::query()->create([
            'user_id' => $compte->id,
            'token_hash' => hash('sha256', $jeton),
            'expires_at' => now()->addHours(self::VALIDITE_HEURES),
            'request_ip' => $ip,
        ]);

        return lien('mot-de-passe.formulaire', [
            'demande' => $demande->id,
            'jeton' => $jeton,
        ]);
    }

    /** Retrouve une demande valide, ou null. */
    public function retrouver(int $id, string $jeton): ?PasswordReset
    {
        $demande = PasswordReset::query()->with('user')->find($id);

        if (! $demande || ! $demande->utilisable()) {
            return null;
        }

        // Comparaison a temps constant : le jeton est un secret.
        return hash_equals($demande->token_hash, hash('sha256', $jeton))
            ? $demande
            : null;
    }

    /** Applique le nouveau mot de passe et consomme le jeton. */
    public function reinitialiser(PasswordReset $demande, string $nouveau): User
    {
        $compte = $demande->user;
        $compte->forceFill(['password' => Hash::make($nouveau)])->save();

        $demande->forceFill(['used_at' => now()])->save();

        return $compte;
    }
}
