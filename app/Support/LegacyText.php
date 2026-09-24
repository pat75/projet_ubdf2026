<?php

namespace App\Support;

/**
 * Point de passage unique de toute chaine reprise de la base ub2020.
 *
 * Les tables legacy sont declarees latin1_swedish_ci mais contiennent des
 * octets UTF-8 bruts : la connexion « legacy » lit donc en latin1 pour
 * recuperer ces octets tels quels (cf. config/database.php).
 *
 * Audit du 2026-09-15 sur 2 031 104 valeurs (11 colonnes textuelles) :
 *   - 2 valeurs se terminent par un octet orphelin (varchar tronque au
 *     milieu d'un caractere accentue) : seul defaut reel du corpus ;
 *   - aucun double encodage, verifie au niveau des octets (recherche des
 *     sequences C383C2 / C383C3 / C3A2C280) sur les colonnes principales.
 * Tout le reste est de l'UTF-8 valide. La correction du double encodage est
 * donc un filet preventif, inactif sur le corpus actuel : elle n'agit que si
 * un marqueur est present ET que le re-decodage donne de l'UTF-8 valide.
 *
 * Les formulaires de contact du legacy passaient le texte saisi par
 * `htmlspecialchars`/`htmlentities` avant de l'ecrire en base (le gabarit
 * de 2019 l'affichait tel quel dans du HTML) : un message stocke peut donc
 * contenir des entites (`di&eacute;t&eacute;ticienne`, `&#039;`) qui, une
 * fois echappees a nouveau par Blade, s'affichent litteralement.
 */
final class LegacyText
{
    /**
     * Sequences produites par un double encodage UTF-8 → latin1 → UTF-8.
     * Leur presence declenche une tentative de re-decodage, jamais imposee.
     */
    private const DOUBLE_ENCODED_MARKERS = [
        'Ã©', 'Ã¨', 'Ã ', 'Ã¢', 'Ãª', 'Ã®', 'Ã´', 'Ã»', 'Ã§', 'Ã«', 'Ã¯', 'Ã¼',
        'Ã‰', 'Ãˆ', 'Ã€', 'Ã‡', 'â€™', 'â€œ', 'â€', 'â€¦', 'â€“', 'Â°', 'Â«', 'Â»',
    ];

    public static function clean(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $value = self::dropTruncatedTail($value);
        $value = self::fixDoubleEncoding($value);

        // Filet de securite : une valeur encore invalide est du Windows-1252.
        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        }

        return self::decodeEntitesHtml($value);
    }

    /**
     * Sans effet si la chaine ne contient aucune entite : un texte qui
     * contient legitimement un « & » ressort inchange.
     */
    private static function decodeEntitesHtml(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Supprime la sequence UTF-8 incomplete en fin de chaine, laissee par un
     * varchar tronque au milieu d'un caractere multi-octets.
     */
    private static function dropTruncatedTail(string $value): string
    {
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        // Au plus 3 octets de continuation possibles pour un caractere UTF-8.
        for ($i = 1; $i <= 3 && $i < strlen($value); $i++) {
            $candidate = substr($value, 0, -$i);

            if (mb_check_encoding($candidate, 'UTF-8')) {
                return $candidate;
            }
        }

        return $value;
    }

    /**
     * Corrige un double encodage, mais seulement si la chaine en porte un
     * marqueur ET que le re-decodage produit de l'UTF-8 valide. Un texte
     * contenant legitimement « Ã » est donc laisse intact.
     */
    private static function fixDoubleEncoding(string $value): string
    {
        if (! self::looksDoubleEncoded($value)) {
            return $value;
        }

        $decoded = @mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');

        if ($decoded === false || $decoded === '' || ! mb_check_encoding($decoded, 'UTF-8')) {
            return $value;
        }

        // Le re-decodage ne doit pas avoir reintroduit d'anomalie.
        return self::looksDoubleEncoded($decoded) ? $value : $decoded;
    }

    private static function looksDoubleEncoded(string $value): bool
    {
        foreach (self::DOUBLE_ENCODED_MARKERS as $marker) {
            if (str_contains($value, $marker)) {
                return true;
            }
        }

        return false;
    }
}
