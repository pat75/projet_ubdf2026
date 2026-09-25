<?php

namespace App\Support;

/**
 * Dossier disque d'un book, segmente par les trois premiers caracteres du
 * login : adolie -> books/a/d/o/adolie.
 *
 * Plusieurs milliers de books dans un seul dossier ralentissent le disque
 * et les outils (ls, rsync, sauvegarde) ; trois niveaux les repartissent.
 * Un login de moins de trois caracteres est complete par « _ » (ab ->
 * a/b/_/ab) : le dossier d'un book est donc toujours au quatrieme niveau,
 * et ne peut jamais se confondre avec un dossier de segment.
 *
 * Seul l'emplacement disque change : les URL publiques restent
 * /books/<login>/..., BookMediaController fait la correspondance.
 */
final class DossierBook
{
    public const RACINE = 'books';

    /** Chemin relatif au disque `public` : books/a/d/o/adolie[/fichier]. */
    public static function relatif(string $login, string $fichier = ''): string
    {
        $segments = str_split(str_pad(mb_strtolower(substr($login, 0, 3)), 3, '_'));
        $dossier = self::RACINE.'/'.implode('/', $segments).'/'.$login;

        return $fichier === '' ? $dossier : $dossier.'/'.ltrim($fichier, '/');
    }

    /** Chemin absolu : storage/app/public/books/a/d/o/adolie[/fichier]. */
    public static function chemin(string $login, string $fichier = ''): string
    {
        return storage_path('app/public/'.self::relatif($login, $fichier));
    }
}
