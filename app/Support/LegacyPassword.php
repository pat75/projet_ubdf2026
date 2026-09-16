<?php

namespace App\Support;

use phpseclib3\Crypt\Rijndael;

/**
 * Dechiffrement des mots de passe de la base ub2020.
 *
 * Le legacy ne hashe pas les mots de passe : il les CHIFFRE, de facon
 * reversible, avec une clé en dur — voir inc/inc_user.php:7496, classe
 * encryptClass. Deux formats coexistent en base :
 *   - encode()   : Rijndael-256 ECB, cle de 18 octets completee a 24 par
 *                  mcrypt (taille valide suivante) — format majoritaire ;
 *   - encode_2() : AES-256-CBC, ajoute au passage a PHP 7.2.
 * Seul encode() prefixe le clair de sa longueur ("<len>-<mdp>") ; encode_2()
 * chiffre le mot de passe tel quel.
 *
 * Consequence : la reprise peut rehasher chaque mot de passe en bcrypt sans
 * demander de reinitialisation aux 78 000 comptes. Cette classe n'a aucune
 * raison d'exister au-dela de la migration ; elle est a supprimer ensuite.
 *
 * mcrypt (PHP <= 7.1) n'existe plus en 8.3, et openssl ne gere pas les blocs
 * de 256 bits : phpseclib est la seule voie.
 */
final class LegacyPassword
{
    private const SECRET = 'jhdfgkj36bh568kjfd';

    /** mcrypt completait la cle de 18 octets jusqu'a 24, taille valide suivante. */
    private const KEY_SIZE = 24;

    private const BLOCK_SIZE = 32;

    private static ?Rijndael $cipher = null;

    /**
     * Rend le mot de passe en clair, ou null si la valeur est illisible.
     */
    public static function decrypt(?string $encrypted): ?string
    {
        if ($encrypted === null || trim($encrypted) === '') {
            return null;
        }

        // encode_2() chiffre le mot de passe tel quel, sans prefixe de longueur.
        $aes = self::decryptAesCbc($encrypted);

        if ($aes !== null) {
            return $aes === '' ? null : $aes;
        }

        return self::unwrap(self::decryptRijndael($encrypted));
    }

    /**
     * encryptClass::encode() — Rijndael-256 ECB, le format majoritaire.
     */
    private static function decryptRijndael(string $encrypted): ?string
    {
        $raw = base64_decode($encrypted, true);

        if ($raw === false || $raw === '' || strlen($raw) % self::BLOCK_SIZE !== 0) {
            return null;
        }

        $plain = self::cipher()->decrypt($raw);

        return $plain === false ? null : $plain;
    }

    /**
     * encryptClass::encode_2() — AES-256-CBC, introduit au passage a PHP 7.2
     * pour les comptes crees ou modifies apres l'abandon de mcrypt.
     * Charge utile : base64(<chiffre base64> . '::' . <iv>).
     */
    private static function decryptAesCbc(string $encrypted): ?string
    {
        $raw = base64_decode($encrypted, true);

        if ($raw === false || ! str_contains($raw, '::')) {
            return null;
        }

        [$payload, $iv] = explode('::', $raw, 2);

        if (strlen($iv) !== openssl_cipher_iv_length('aes-256-cbc')) {
            return null;
        }

        $plain = @openssl_decrypt($payload, 'aes-256-cbc', self::SECRET, 0, $iv);

        return $plain === false ? null : $plain;
    }

    /**
     * Retire le prefixe "<longueur>-" et le padding \0 laisse par mcrypt.
     */
    private static function unwrap(?string $plain): ?string
    {
        if ($plain === null || ! preg_match('/^(\d+)-/', $plain, $matches)) {
            return null;
        }

        $password = substr($plain, strlen($matches[1]) + 1, (int) $matches[1]);

        return $password === false || $password === '' ? null : $password;
    }

    private static function cipher(): Rijndael
    {
        if (self::$cipher === null) {
            $cipher = new Rijndael('ecb');
            $cipher->setBlockLength(self::BLOCK_SIZE * 8);
            $cipher->setKey(str_pad(self::SECRET, self::KEY_SIZE, "\0"));
            $cipher->disablePadding();

            self::$cipher = $cipher;
        }

        return self::$cipher;
    }
}
