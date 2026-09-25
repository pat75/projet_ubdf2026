<?php

namespace App\Services\Captcha;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Captcha local : un code de quatre caracteres a recopier, dessine en
 * SVG (noir sur fond blanc hachure), sans service tiers.
 *
 * Chaque formulaire a son propre code en session (`captcha.<formulaire>`),
 * pour qu'un visiteur puisse avoir plusieurs formulaires ouverts sans que
 * l'un invalide l'autre. Le code est retire de la session a la premiere
 * verification, reussie ou non : une image ne sert qu'une fois.
 *
 * La session etant propre a chaque hote, l'image doit etre servie par le
 * meme hote que le formulaire (portail ou sous-domaine du book) : voir
 * CaptchaController et ses deux routes.
 *
 * Utilisation : GET /captcha/{formulaire} pour l'image, regle
 * App\Rules\CaptchaValide('<formulaire>') pour la verification.
 */
class Captcha
{
    /** Formulaires qui disposent d'un captcha. */
    public const FORMULAIRES = ['contact', 'contact_book', 'inscription'];

    /** Sans I, O ni L : ils se confondent avec 1 et 0. */
    public const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ0123456789';

    public const LONGUEUR = 4;

    public const LARGEUR = 150;

    public const HAUTEUR = 50;

    /** Tire un nouveau code pour ce formulaire, le garde en session et le rend. */
    public function generer(string $formulaire): string
    {
        $code = '';

        for ($i = 0; $i < self::LONGUEUR; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        Session::put($this->cle($formulaire), $code);

        return $code;
    }

    /** Compare la saisie au code attendu (casse et espaces ignores), puis l'oublie. */
    public function verifier(string $formulaire, ?string $saisie): bool
    {
        $attendu = (string) Session::pull($this->cle($formulaire), '');
        $donne = Str::upper(preg_replace('/\s+/', '', (string) $saisie));

        return $attendu !== '' && hash_equals($attendu, $donne);
    }

    /**
     * Image du code : caracteres noirs, legerement tournes et decales, sur
     * un fond blanc hachure en deux sens ; quelques traits fins traversent
     * les lettres. De quoi gener un OCR simple sans gener la lecture.
     */
    public function svg(string $code): string
    {
        $l = self::LARGEUR;
        $h = self::HAUTEUR;
        $pas = ($l - 20) / self::LONGUEUR;
        $angle = random_int(30, 60);

        $lettres = '';
        foreach (str_split($code) as $rang => $lettre) {
            $x = 16 + $rang * $pas + random_int(-3, 3);
            $y = $h - random_int(12, 16);
            $lettres .= sprintf(
                '<text x="%d" y="%d" font-size="%d" transform="rotate(%d %d %d)">%s</text>',
                $x, $y, random_int(28, 33), random_int(-18, 18), $x + 10, $y - 10, e($lettre)
            );
        }

        $traits = '';
        for ($i = 0; $i < 3; $i++) {
            $traits .= sprintf(
                '<path d="M0 %d Q %d %d %d %d" stroke="#000" stroke-width="1" fill="none" opacity=".55"/>',
                random_int(8, $h - 8), random_int(30, $l - 30), random_int(0, $h), $l, random_int(8, $h - 8)
            );
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$l}" height="{$h}" viewBox="0 0 {$l} {$h}" role="img" aria-label="Code à recopier">
<defs>
<pattern id="h1" width="6" height="6" patternUnits="userSpaceOnUse" patternTransform="rotate({$angle})"><line x1="0" y1="0" x2="0" y2="6" stroke="#9a9a9a" stroke-width="1"/></pattern>
<pattern id="h2" width="9" height="9" patternUnits="userSpaceOnUse" patternTransform="rotate(-{$angle})"><line x1="0" y1="0" x2="0" y2="9" stroke="#c4c4c4" stroke-width="1"/></pattern>
</defs>
<rect width="100%" height="100%" fill="#fff"/>
<rect width="100%" height="100%" fill="url(#h1)"/>
<rect width="100%" height="100%" fill="url(#h2)"/>
<g font-family="'Courier New', Courier, monospace" font-weight="bold" fill="#000" stroke="#fff" stroke-width="1.2" paint-order="stroke">{$lettres}</g>
{$traits}
</svg>
SVG;
    }

    private function cle(string $formulaire): string
    {
        return 'captcha.'.$formulaire;
    }
}
