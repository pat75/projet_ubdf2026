<?php

namespace App\Support;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Captcha image du formulaire de contact.
 *
 * Reprend le principe du legacy — un code de cinq caracteres tire au hasard,
 * garde en session, compare sans tenir compte de la casse — avec deux
 * corrections :
 *
 *  - le code est **toujours** retire de la session apres verification, y
 *    compris en cas d'echec. Le legacy ne l'effaçait qu'en cas de succes, ce
 *    qui laissait reessayer indefiniment sur la meme image ;
 *  - la comparaison passe par `hash_equals`.
 */
class Captcha implements ValidationRule
{
    public const SESSION = 'captcha_code';

    /** Genere un code, le place en session et le rend. */
    public static function generer(): string
    {
        $alphabet = (string) config('messagerie.captcha.alphabet');
        $longueur = (int) config('messagerie.captcha.longueur');

        $code = '';

        for ($i = 0; $i < $longueur; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        Session::put(self::SESSION, $code);

        return $code;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $attendu = (string) Session::pull(self::SESSION, '');
        $donne = Str::upper(trim((string) $value));

        if ($attendu === '' || ! hash_equals($attendu, $donne)) {
            $fail('Le code recopié ne correspond pas à l’image.');
        }
    }
}
