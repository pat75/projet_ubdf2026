<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Marque sous laquelle une requete est servie : Ultra-book ou Dustfolio.
 *
 * Deux sites, un seul code et une seule base : chaque compte porte sa marque
 * (`users.brand`, reprise de `inc_user.us_view`), et chaque hote determine
 * celle du visiteur.
 */
final class Marque
{
    public function __construct(
        public readonly string $code,
        public readonly string $nom,
        public readonly string $email,
        public readonly string $locale,
        public readonly string $assets,
        public readonly string $logo,
        public readonly string $canonique,
    ) {}

    /**
     * Marque correspondant a un hote HTTP.
     *
     * La comparaison se fait par egalite, sur l'hote prive de son port et de
     * son prefixe « www. ». Un hote inconnu rend la marque par defaut : le
     * portail doit repondre meme derriere un alias oublie.
     */
    public static function depuisHote(?string $hote): self
    {
        $hote = Str::lower(Str::before((string) $hote, ':'));
        $hote = Str::after($hote, 'www.') ?: $hote;

        foreach (config('marques.marques') as $code => $marque) {
            foreach ($marque['hotes'] as $candidat) {
                if ($hote === Str::lower($candidat)) {
                    return self::depuisCode($code);
                }
            }
        }

        return self::defaut();
    }

    public static function depuisCode(string $code): self
    {
        $marque = config('marques.marques.'.$code);

        if (! $marque) {
            return self::defaut();
        }

        return new self(
            code: $code,
            nom: $marque['nom'],
            email: $marque['email'],
            locale: $marque['locale'],
            assets: $marque['assets'],
            logo: $marque['logo'],
            canonique: $marque['canonique'],
        );
    }

    public static function defaut(): self
    {
        return self::depuisCode((string) config('marques.defaut'));
    }

    public function estDefaut(): bool
    {
        return $this->code === config('marques.defaut');
    }

    /**
     * Adapte un contenu editorial a la marque.
     *
     * Dustfolio n'a jamais eu de pages a lui : le legacy servait celles
     * d'Ultra-book en y substituant le nom au vol. Le comportement est
     * conserve, mais la table des substitutions est declaree dans
     * `config/marques.php` au lieu d'etre dispersee dans `action.php`.
     */
    public function adapter(?string $texte): ?string
    {
        if ($texte === null || $texte === '') {
            return $texte;
        }

        foreach (config('marques.substitutions.'.$this->code, []) as $cherche => $remplace) {
            $texte = preg_replace('/'.preg_quote($cherche, '/').'/i', $remplace, $texte) ?? $texte;
        }

        return $texte;
    }

    /**
     * Chemin d'une ressource propre a la marque.
     *
     * Le legacy repartissait ces fichiers par dossier — `img_front` et
     * `img_front_df` — et non par suffixe de nom de fichier.
     */
    public function asset(string $chemin): string
    {
        return '/img_front'.$this->assets.'/'.ltrim($chemin, '/');
    }

    /** Une etiquette de sous-domaine reservee ne designe jamais un book. */
    public static function sousDomaineReserve(string $label): bool
    {
        return in_array(Str::lower($label), config('marques.sous_domaines_reserves', []), true);
    }
}
