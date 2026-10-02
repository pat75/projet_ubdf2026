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
        /** @var list<string> Langues servies, la premiere etant la langue par defaut. */
        public readonly array $langues,
        public readonly string $assets,
        public readonly string $logo,
        /** Version claire du logo, pour les fonds sombres. */
        public readonly string $logoClair,
        public readonly string $canonique,
        public readonly string $titre,
        public readonly string $description,
        /** Domaine des books de la marque : <login>.<domaineBooks>. */
        public readonly string $domaineBooks = '',
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
            langues: array_values(array_filter(
                $marque['langues'],
                fn (string $code) => array_key_exists($code, config('langues.disponibles', [])),
            )),
            assets: $marque['assets'],
            logo: $marque['logo'],
            logoClair: $marque['logo_clair'] ?? $marque['logo'],
            canonique: $marque['canonique'],
            titre: $marque['titre'],
            description: $marque['description'],
            // `BOOK_DOMAIN=` vide dans le .env donne '' et non null : ?: et
            // non ??, sinon le lien du book devient https://<login>./
            domaineBooks: ($marque['domaine_books'] ?? '') ?: (string) config('ubdf.book_domain'),
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

    /** Langue servie a defaut d'indication contraire : la premiere declaree. */
    public function locale(): string
    {
        return $this->langues[0] ?? 'fr';
    }

    /**
     * Une marque multilingue porte la langue en tete de chaque URL.
     *
     * Une marque monolingue n'en porte aucune : publier `/fr/illustrateur`
     * a cote de `/illustrateur` donnerait deux adresses pour la meme page
     * francaise.
     */
    public function multilingue(): bool
    {
        return count($this->langues) > 1;
    }

    public function sert(string $langue): bool
    {
        return in_array($langue, $this->langues, true);
    }

    /** Segment de langue a placer en tete des URL, vide si monolingue. */
    public function prefixe(?string $langue = null): string
    {
        return $this->multilingue() ? ($langue ?? app()->getLocale()) : '';
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
     * Titre par defaut, traduit sur une marque multilingue.
     *
     * Ultra-book reste en francais : la chaine passe telle quelle. Sur
     * Dustfolio, `__()` suit la langue imposee par l'URL.
     */
    public function titre(): string
    {
        return $this->multilingue() ? __($this->titre) : $this->titre;
    }

    public function description(): string
    {
        return $this->multilingue() ? __($this->description) : $this->description;
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

    /**
     * Marque dont `$domaine` est le domaine des books, ou null.
     */
    public static function depuisDomaineBooks(string $domaine): ?self
    {
        foreach (array_keys(config('marques.marques')) as $code) {
            $marque = self::depuisCode($code);
            if (Str::lower($marque->domaineBooks) === Str::lower($domaine)) {
                return $marque;
            }
        }

        return null;
    }

    /** @return list<string> domaines des books de toutes les marques */
    public static function domainesBooks(): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn (string $code) => self::depuisCode($code)->domaineBooks,
            array_keys(config('marques.marques')),
        ))));
    }
}
