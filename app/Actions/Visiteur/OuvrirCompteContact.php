<?php

namespace App\Actions\Visiteur;

use App\Actions\Auth\IdentifierCompte;
use App\Models\User;
use App\Models\Visitor;
use App\Support\Marque;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Case « Créer mon compte » du formulaire de contact de la visionneuse.
 *
 * Adresse inconnue : ouvre un compte visiteur, comme le memo book.
 * Adresse connue (visiteur ou creatif) : le mot de passe connecte a ce
 * compte, sous son propre guard — un creatif retrouve son espace.
 *
 * Le compte est ouvert avant le depot de la demande : un mot de passe
 * refuse n'envoie rien, pour que le visiteur corrige et renvoie.
 */
class OuvrirCompteContact
{
    /** Essais de mot de passe par adresse + IP, comme la connexion. */
    private const ESSAIS_MAX = 5;

    private const BLOCAGE_SECONDES = 600;

    public function __construct(
        private readonly IdentifierCompte $identifier,
        private readonly CreerCompteVisiteur $creer,
    ) {}

    /** @throws ValidationException sur le champ `password` */
    public function executer(string $email, string $motDePasse, string $nom, Marque $marque, ?string $ip): User|Visitor
    {
        $email = mb_strtolower(trim($email));

        $compte = $this->existe($email)
            ? $this->retrouver($email, $motDePasse, $ip)
            : $this->creer->executer($email, $motDePasse, [], $marque, $ip, ...$this->decouper($nom));

        Auth::guard($compte instanceof Visitor ? 'visitor' : 'web')->login($compte, remember: true);
        request()->session()->regenerate();

        return $compte;
    }

    private function existe(string $email): bool
    {
        return Visitor::query()->where('email', $email)->exists()
            || User::query()->where('email', $email)->exists();
    }

    private function retrouver(string $email, string $motDePasse, ?string $ip): User|Visitor
    {
        $cle = 'contact-compte|'.$email.'|'.$ip;

        if (RateLimiter::tooManyAttempts($cle, self::ESSAIS_MAX)) {
            throw ValidationException::withMessages([
                'password' => __('Trop de tentatives. Réessayez dans quelques minutes.'),
            ]);
        }

        $compte = $this->identifier->executer($email, $motDePasse);

        if ($compte instanceof User || $compte instanceof Visitor) {
            RateLimiter::clear($cle);

            return $compte;
        }

        RateLimiter::hit($cle, self::BLOCAGE_SECONDES);

        throw ValidationException::withMessages(['password' => $compte === IdentifierCompte::AMBIGU
            ? __('Plusieurs books utilisent cette adresse : connectez-vous avec votre identifiant.')
            : __('Cette adresse a déjà un compte : mot de passe incorrect.')]);
    }

    /** « Claire Dupont » → prenom « Claire », nom « Dupont ». */
    private function decouper(string $nom): array
    {
        $mots = preg_split('/\s+/', trim($nom), 2);

        return ['prenom' => $mots[0] ?: null, 'nom' => $mots[1] ?? null];
    }
}
