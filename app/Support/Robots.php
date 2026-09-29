<?php

namespace App\Support;

/**
 * Contenu de robots.txt.
 *
 * Le serveur web sert `/robots.txt` comme un fichier statique, sans jamais
 * passer la main a Laravel (`location = /robots.txt` des blocs Valet et
 * Forge). Le fichier `public/robots.txt` est donc ecrit par la commande
 * planifiee `ubdf:robots` a partir d'ici ; la route du meme nom ne sert
 * que si le serveur la laisse passer.
 *
 * Un seul fichier sert les deux marques et les books : il annonce le
 * sitemap de chacune. Le protocole sitemaps admet, dans robots.txt, un
 * sitemap situe sur un autre hote.
 */
final class Robots
{
    public static function contenu(): string
    {
        $lignes = [
            'User-agent: ia_archiver',
            'Disallow: /',
            '',
            'User-agent: *',
            'Disallow: /espace',
            'Disallow: /admin',
            'Disallow: /messages/',
            'Disallow: /newsletter/desabonnement/',
            '',
        ];

        foreach (array_keys(config('marques.marques')) as $code) {
            $lignes[] = 'Sitemap: '.rtrim(Marque::depuisCode($code)->canonique, '/').'/sitemap.xml';
        }

        return implode("\n", $lignes)."\n";
    }
}
