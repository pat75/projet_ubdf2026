<?php

namespace App\Services\Book;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

/**
 * Inclusion des gabarits de theme, avec la semantique de Savant.
 *
 * Dans le legacy, `include $this->loadTemplate('zoom2016/_header.tlp.php')`
 * incluait le fichier dans la portee courante : le gabarit inclus voyait
 * les variables locales de l'appelant (`$typePage`, `$content`,
 * `$obj_pref`…), et plusieurs gabarits en dependent. `@include` de Blade
 * ne partage pas cette portee.
 *
 * On reproduit donc l'inclusion telle quelle : `chemin()` rend le chemin
 * du gabarit Blade **compile** (le compilant au besoin), que le gabarit
 * appelant inclut avec un `include` PHP ordinaire.
 */
class Gabarit
{
    /** Repertoire des vues des themes. */
    public const RACINE = 'book/themes';

    /** Gabarits qui vivaient a la racine de 2011_html_pages_v2/ (theme 2010). */
    public const DOSSIER_RACINE = '_racine';

    /**
     * Rend le point d'entree d'un theme, comme `$tpl->display()` du legacy.
     *
     * Le code des gabarits est celui du legacy : il lit des index et des
     * variables qui n'existent pas toujours, ce que PHP 7 taisait sous
     * `error_reporting(E_ALL & ~E_NOTICE)`. En PHP 8 ce sont des
     * avertissements, et Laravel les convertit en exceptions. La tolerance
     * est donc retablie **pendant ce rendu seulement** : le reste de
     * l'application garde le niveau strict.
     */
    public static function rendre(string $vue, ContexteBook $b): string
    {
        $niveau = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        self::chargerFonctions();

        // Les gabarits 2020 lisent `global $user_book, $token` pour le mode
        // edition. Un bouchon qui refuse tout : l'edition est en phase 5.
        $GLOBALS['user_book'] ??= new class
        {
            public function __call(string $methode, array $arguments): bool
            {
                return false;
            }
        };
        $GLOBALS['token'] ??= null;

        try {
            ob_start();
            include self::chemin($vue.'.php');

            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();

            throw $e;
        } finally {
            error_reporting($niveau);
        }
    }

    /**
     * Fonctions des gabarits (<dossier>/_fonctions.php), de tous les themes :
     * un theme peut inclure un gabarit d'un autre dossier (Pinter inclut
     * ceux de la racine). Les noms sont prefixes par dossier, sans conflit.
     */
    private static function chargerFonctions(): void
    {
        foreach (glob(resource_path('views/'.self::RACINE.'/*/_fonctions.php')) as $fichier) {
            require_once $fichier;
        }
    }

    /**
     * @param  string  $legacy  Chemin tel que le legacy le passait a
     *                          loadTemplate(), ex. `zoom2016/_header.tlp.php`.
     */
    public static function chemin(string $legacy): string
    {
        $source = resource_path('views/'.self::RACINE.'/'.self::nomDeVue($legacy).'.blade.php');

        if (! File::exists($source)) {
            throw new \RuntimeException("Gabarit de theme introuvable : {$legacy}");
        }

        $compilateur = Blade::getFacadeRoot();

        if ($compilateur->isExpired($source)) {
            $compilateur->compile($source);
        }

        return $compilateur->getCompiledPath($source);
    }

    /**
     * Remplace les `setcookie()` des gabarits.
     *
     * Ils servaient a entretenir le mode d'edition du book (`us_pr`,
     * `us_pr_login`) depuis le gabarit lui-meme. Un gabarit n'ecrit plus
     * d'en-tete : l'edition du book releve de l'espace creatif (phase 5).
     */
    public static function ignorer(mixed ...$arguments): bool
    {
        return true;
    }

    /** `zoom2016/_header.tlp.php` -> `zoom2016/_header`. */
    public static function nomDeVue(string $legacy): string
    {
        $legacy = ltrim(str_replace('\\', '/', $legacy), '/');
        // Le theme de 2010 n'a pas de dossier : url_mdl vide donne « /x.php ».
        $dossier = str_contains($legacy, '/') ? dirname($legacy) : self::DOSSIER_RACINE;
        $dossier = $dossier === '' || $dossier === '.' ? self::DOSSIER_RACINE : $dossier;
        $fichier = preg_replace('/(\.tlp)?\.php$/', '', basename($legacy));

        return $dossier.'/'.$fichier;
    }
}
