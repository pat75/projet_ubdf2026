<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Langue d'affichage du portail.
 *
 * Deux regimes, selon la marque :
 *
 *  - **Ultra-book** est monolingue. Ses URL ne portent pas de segment de
 *    langue, et `/fr/illustrateur` n'existe pas : publier deux adresses
 *    pour la meme page francaise n'apporterait rien et diluerait son
 *    referencement.
 *  - **Dustfolio** est multilingue, anglophone par defaut. Chaque page
 *    porte sa langue en tete d'URL — `/en/illustrator`, `/fr/illustrateur`
 *    — ce qui donne une adresse distincte et indexable par langue.
 *
 * Le legacy travaillait en identifiants POSIX (`fr_FR`, `en_US`) parce que
 * gettext en a besoin pour `setlocale`. Ils restent compris a l'entree :
 * les visiteurs de l'ancien site portent un cookie `lang=fr_FR`.
 */
final class Langue
{
    /** Partage avec les books des sous-domaines, d'ou le nom explicite. */
    public const COOKIE = 'ub_lang';

    /** Nom du cookie pose par le site de 2019. */
    public const COOKIE_LEGACY = 'lang';

    /** @return list<string> */
    public static function codes(): array
    {
        return array_keys(config('langues.disponibles', []));
    }

    public static function supportee(?string $code): bool
    {
        return $code !== null && in_array($code, self::codes(), true);
    }

    /**
     * Ramene une valeur quelconque a un code servi, ou null.
     *
     * Accepte la forme POSIX (`fr_FR`) et la forme IETF (`en-GB`).
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
     * Meilleure langue d'apres l'en-tete Accept-Language, parmi celles que
     * la marque sert reellement.
     *
     * @param  list<string>  $preferees
     * @param  list<string>  $servies
     */
    public static function depuisNavigateur(array $preferees, array $servies): ?string
    {
        foreach ($preferees as $preferee) {
            $code = self::normaliser($preferee);

            if ($code !== null && in_array($code, $servies, true)) {
                return $code;
            }
        }

        return null;
    }

    /** Identifiant POSIX, pour la balise og:locale. */
    public static function posix(string $code): string
    {
        return config('langues.disponibles.'.$code.'.posix', 'fr_FR');
    }

    public static function nom(string $code): string
    {
        return config('langues.disponibles.'.$code.'.nom', $code);
    }
}
