<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Contenu de robots.txt, propre a la marque de l'hote.
 *
 * Chaque domaine n'annonce que son propre sitemap : un robots.txt commun
 * declarait sur ultra-book.com le sitemap de dustfolio.com (et inversement),
 * ce qui reliait les deux marques aux yeux des moteurs.
 *
 * Le serveur web doit donc passer `/robots.txt` a Laravel (plus de fichier
 * statique dans public/).
 */
final class Robots
{
    public static function contenu(Marque $marque): string
    {
        return implode("\n", [
            'User-agent: ia_archiver',
            'Disallow: /',
            '',
            'User-agent: *',
            'Disallow: /espace',
            'Disallow: /admin',
            'Disallow: /messages/',
            'Disallow: /newsletter/desabonnement/',
            '',
            'Sitemap: '.rtrim($marque->canonique, '/').'/sitemap.xml',
        ])."\n";
    }

    /**
     * Marque d'un hote, sous-domaines de books compris : <login>.<domaine_books>
     * n'est pas dans la liste des hotes du portail.
     */
    public static function marqueDeHote(string $hote): Marque
    {
        $hote = Str::lower(Str::before($hote, ':'));

        foreach (array_keys(config('marques.marques')) as $code) {
            $marque = Marque::depuisCode($code);
            if ($marque->domaineBooks !== '' && Str::endsWith($hote, '.'.Str::lower($marque->domaineBooks))) {
                return $marque;
            }
        }

        return Marque::depuisHote($hote);
    }
}
