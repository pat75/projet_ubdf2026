<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Langue d'affichage du portail.
 *
 * Le legacy travaillait en identifiants POSIX (`fr_FR`, `en_US`, `ja_JP`)
 * parce que gettext en a besoin pour `setlocale`. Laravel n'en a pas
 * besoin : la cle courte suffit, et c'est deja elle qui apparait dans les
 * URL de bascule (`/fr`, `/en`, `/ja`) declarees dans `.htaccess`.
 */
final class Langue
{
    public const COOKIE = 'lang';

    /** @return list<string> */
    public static function codes(): array
    {
        return array_keys(config('langues.langues', []));
    }

    public static function supportee(?string $code): bool
    {
        return $code !== null && in_array($code, self::codes(), true);
    }

    /**
     * Ramene une valeur quelconque a un code supporte, ou null.
     *
     * Accepte la forme POSIX du legacy : les visiteurs de l'ancien site
     * portent un cookie `lang=fr_FR`, qui doit continuer a etre compris.
     */
    public static function normaliser(?string $valeur): ?string
    {
        if ($valeur === null || $valeur === '') {
            return null;
        }

        $code = Str::lower(Str::before(str_replace('-', '_', $valeur), '_'));

        return self::supportee($code) ? $code : null;
    }

    /**
     * Meilleure langue d'apres l'en-tete Accept-Language.
     *
     * Le legacy ne la regardait pas : un visiteur japonais arrivait en
     * francais tant qu'il n'avait pas trouve le selecteur.
     *
     * @param  list<string>  $preferees
     */
    public static function depuisNavigateur(array $preferees): ?string
    {
        foreach ($preferees as $preferee) {
            if ($code = self::normaliser($preferee)) {
                return $code;
            }
        }

        return null;
    }

    /** Identifiant POSIX, pour la balise og:locale. */
    public static function posix(string $code): string
    {
        return config('langues.langues.'.$code.'.posix', 'fr_FR');
    }

    public static function nom(string $code): string
    {
        return config('langues.langues.'.$code.'.nom', $code);
    }
}
