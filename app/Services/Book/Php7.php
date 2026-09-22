<?php

namespace App\Services\Book;

/**
 * Comportement PHP 7 de quelques fonctions, pour les gabarits de book.
 *
 * Les gabarits du legacy appellent `count()`, `in_array()`, `reset()`…
 * sur des valeurs qui sont parfois `null` ou scalaires. PHP 7 renvoyait
 * alors 0, false ou null avec un simple avertissement ; PHP 8 leve une
 * TypeError et la page tombe. Le convertisseur (_outils/porter_gabarits.py)
 * redirige ces appels ici, dans les gabarits de book seulement.
 *
 * Chaque methode reproduit la valeur que PHP 7 renvoyait, et appelle la
 * fonction native des que l'argument est valide.
 */
final class Php7
{
    public static function count($valeur, int $mode = COUNT_NORMAL): int
    {
        if (is_array($valeur) || $valeur instanceof \Countable) {
            return \count($valeur, $mode);
        }

        // PHP 7 : count(null) = 0, count(scalaire) = 1.
        return $valeur === null ? 0 : 1;
    }

    public static function sizeof($valeur, int $mode = COUNT_NORMAL): int
    {
        return self::count($valeur, $mode);
    }

    public static function in_array($aiguille, $tableau, bool $strict = false): bool
    {
        return is_array($tableau) && \in_array($aiguille, $tableau, $strict);
    }

    public static function array_key_exists($cle, $tableau): bool
    {
        return is_array($tableau) && \array_key_exists($cle ?? '', $tableau);
    }

    public static function array_keys($tableau, ...$reste): ?array
    {
        return is_array($tableau) ? \array_keys($tableau, ...$reste) : null;
    }

    public static function array_values($tableau): ?array
    {
        return is_array($tableau) ? \array_values($tableau) : null;
    }

    public static function array_merge(...$tableaux): ?array
    {
        foreach ($tableaux as $t) {
            if (! is_array($t)) {
                return null;
            }
        }

        return \array_merge(...$tableaux);
    }

    public static function array_slice($tableau, int $debut, ?int $longueur = null, bool $cles = false): ?array
    {
        return is_array($tableau) ? \array_slice($tableau, $debut, $longueur, $cles) : null;
    }

    public static function array_flip($tableau): ?array
    {
        return is_array($tableau) ? @\array_flip($tableau) : null;
    }

    public static function array_reverse($tableau, bool $cles = false): ?array
    {
        return is_array($tableau) ? \array_reverse($tableau, $cles) : null;
    }

    public static function array_search($aiguille, $tableau, bool $strict = false)
    {
        return is_array($tableau) ? \array_search($aiguille, $tableau, $strict) : null;
    }

    public static function implode($a, $b = null): string
    {
        // PHP 7 acceptait aussi l'ordre (tableau, separateur).
        [$separateur, $morceaux] = is_array($a) ? [(string) $b, $a] : [(string) $a, $b];

        return is_array($morceaux) ? \implode($separateur, $morceaux) : '';
    }

    public static function array_pop(&$tableau)
    {
        return is_array($tableau) ? \array_pop($tableau) : null;
    }

    public static function array_shift(&$tableau)
    {
        return is_array($tableau) ? \array_shift($tableau) : null;
    }

    public static function reset(&$tableau)
    {
        return is_array($tableau) || is_object($tableau) ? @\reset($tableau) : false;
    }

    public static function end(&$tableau)
    {
        return is_array($tableau) || is_object($tableau) ? @\end($tableau) : false;
    }

    public static function key($tableau)
    {
        return is_array($tableau) || is_object($tableau) ? @\key($tableau) : null;
    }

    public static function current($tableau)
    {
        return is_array($tableau) || is_object($tableau) ? @\current($tableau) : false;
    }

    public static function max(...$valeurs)
    {
        $valeurs = count($valeurs) === 1 && is_array($valeurs[0]) ? $valeurs[0] : $valeurs;

        return $valeurs === [] ? false : \max($valeurs);
    }

    public static function min(...$valeurs)
    {
        $valeurs = count($valeurs) === 1 && is_array($valeurs[0]) ? $valeurs[0] : $valeurs;

        return $valeurs === [] ? false : \min($valeurs);
    }
}
