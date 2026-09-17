<?php

namespace App\Services\Images;

use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Produit les declinaisons a la demande et les met en cache.
 *
 * Remplace les neuf dossiers pre-generes par book (5 276 books x 9) et
 * phpThumb. Trois differences avec le legacy :
 *
 *   - rien n'est fabrique tant que personne ne l'a demande ;
 *   - seules les declinaisons nommees existent, les dimensions ne viennent
 *     plus de l'URL ;
 *   - une source est refusee si elle depasse `images.pixels_max`, avant
 *     decompression. Une image de 200 Mo de pixels dans un format
 *     compressible tient en quelques kilo-octets sur le disque ; c'est
 *     l'ouvrir qui sature la memoire, pas la lire.
 */
class GenerateurImages
{
    public function __construct(private readonly ImageManager $manager) {}

    /**
     * Chemin absolu de la declinaison, produite si besoin.
     *
     * Retourne null quand la source est absente ou illisible : l'appelant
     * sert alors l'image par defaut, comme le faisait le .htaccess de 2019.
     */
    public function produire(string $source, Declinaison $declinaison): ?string
    {
        if (! is_file($source)) {
            return null;
        }

        $cible = $this->cheminCache($source, $declinaison);

        // Le cache est valide tant qu'il est plus recent que sa source :
        // remplacer un visuel suffit a le perimer, sans purge a faire.
        if (is_file($cible) && filemtime($cible) >= filemtime($source)) {
            return $cible;
        }

        if (! $this->acceptable($source)) {
            return null;
        }

        try {
            // `decodePath` et non `read` : Intervention 4.3 a renomme la
            // methode, `read` n'existe plus.
            $image = $this->manager->decodePath($source);
        } catch (\Throwable) {
            // 23 % des lignes de la table source n'ont pas de fichier, et
            // certains fichiers presents ne sont pas des images.
            return null;
        }

        $image = $this->redimensionner($image, $declinaison);

        @mkdir(dirname($cible), 0o755, recursive: true);
        $image->save($cible, quality: config('images.qualite'));

        return is_file($cible) ? $cible : null;
    }

    /**
     * `scaleDown` et non `scale` : une source plus petite que la boite garde
     * sa taille. C'est ce que fait le legacy — une source de 500x500 ressort
     * inchangee en `ptf_medium`, dont la boite fait 550 de large — et
     * agrandir ne ferait qu'ajouter du flou.
     */
    private function redimensionner(ImageInterface $image, Declinaison $declinaison): ImageInterface
    {
        if ($declinaison->rogne()) {
            return $image->cover($declinaison->largeur, $declinaison->hauteur);
        }

        return $image->scaleDown($declinaison->largeur, $declinaison->hauteur);
    }

    /** Refuse une source dont la surface en pixels saturerait la memoire. */
    private function acceptable(string $source): bool
    {
        $taille = @getimagesize($source);

        if ($taille === false) {
            return false;
        }

        return config('images.pixels_max') >= $taille[0] * $taille[1];
    }

    /**
     * Chemin de cache.
     *
     * Le nom de fichier d'origine est conserve en fin de chemin : il aide a
     * lire le cache et donne son nom au telechargement. Le hachage qui le
     * precede porte le chemin complet de la source, ce qui evite toute
     * collision entre deux books.
     */
    public function cheminCache(string $source, Declinaison $declinaison): string
    {
        $empreinte = substr(hash('xxh128', $source), 0, 12);

        return storage_path(implode('/', [
            'app/public',
            config('images.cache'),
            $declinaison->nom,
            substr($empreinte, 0, 2),
            $empreinte.'_'.basename($source),
        ]));
    }
}
