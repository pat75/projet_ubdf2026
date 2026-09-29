<?php

namespace App\Services\Espace;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\ByteMatrix;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Facades\Cache;

/**
 * Code QR du book, affiche au tableau de bord (SVG) et dans le PDF du
 * memoBook (PNG).
 *
 * Le legacy appelait un service tiers, `qrcode.tec-it.com`, en lui
 * passant l'adresse du book : chaque affichage du tableau de bord faisait
 * connaitre le book a une societe exterieure, et le jour ou ce service
 * tombe, l'image disparait. On le fabrique donc ici, sans reseau.
 *
 * Le rendu est un SVG ecrit a la main a partir de la matrice : la
 * bibliotheque sait produire du SVG, mais en tirant `ext-dom` derriere
 * elle, absente de certains hebergements.
 */
class CodeQr
{
    public function svg(string $donnees, int $taille = 100): string
    {
        return Cache::remember(
            'qr:'.$taille.':'.sha1($donnees),
            now()->addDays(30),
            fn () => $this->fabriquer($donnees, $taille),
        );
    }

    /**
     * Le meme code en PNG, pour dompdf (PDF du memoBook) : son moteur SVG
     * ignore le viewBox et rend le code minuscule. `$module` : pixels par
     * module, assez pour rester net a l'impression. Rendu en base64 : le
     * cache en base ne stocke pas de binaire.
     */
    public function pngBase64(string $donnees, int $module = 8): string
    {
        return Cache::remember('qr-png:'.$module.':'.sha1($donnees), now()->addDays(30), function () use ($donnees, $module) {
            $matrice = $this->matrice($donnees);
            $cotes = $matrice->getWidth();
            $marge = 2;
            $total = ($cotes + $marge * 2) * $module;

            $image = imagecreate($total, $total);
            imagecolorallocate($image, 255, 255, 255);
            $noir = imagecolorallocate($image, 0, 0, 0);

            for ($y = 0; $y < $cotes; $y++) {
                for ($x = 0; $x < $cotes; $x++) {
                    if ($matrice->get($x, $y) === 1) {
                        $gauche = ($x + $marge) * $module;
                        $haut = ($y + $marge) * $module;
                        imagefilledrectangle($image, $gauche, $haut, $gauche + $module - 1, $haut + $module - 1, $noir);
                    }
                }
            }

            ob_start();
            imagepng($image);
            imagedestroy($image);

            return base64_encode((string) ob_get_clean());
        });
    }

    private function matrice(string $donnees): ByteMatrix
    {
        // Correction d'erreur la plus legere : le code reste petit, et il
        // est lu a l'ecran ou sur un imprime propre, pas sur un carton
        // abime.
        return Encoder::encode($donnees, ErrorCorrectionLevel::L(), Encoder::DEFAULT_BYTE_MODE_ECODING)
            ->getMatrix();
    }

    private function fabriquer(string $donnees, int $taille): string
    {
        $matrice = $this->matrice($donnees);

        $cotes = $matrice->getWidth();
        $marge = 2;
        $total = $cotes + $marge * 2;

        $carres = '';

        for ($y = 0; $y < $cotes; $y++) {
            for ($x = 0; $x < $cotes; $x++) {
                if ($matrice->get($x, $y) === 1) {
                    $carres .= '<rect x="'.($x + $marge).'" y="'.($y + $marge).'" width="1" height="1"/>';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$total.' '.$total.'"'
            .' width="'.$taille.'" height="'.$taille.'" shape-rendering="crispEdges" role="img">'
            .'<rect width="'.$total.'" height="'.$total.'" fill="#ffffff"/>'
            .'<g fill="#000000">'.$carres.'</g></svg>';
    }
}
