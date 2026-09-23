<?php

namespace App\Services\Espace;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Facades\Cache;

/**
 * Code QR du book, affiche au tableau de bord.
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

    private function fabriquer(string $donnees, int $taille): string
    {
        // Correction d'erreur la plus legere : le code reste petit, et il
        // est lu a l'ecran ou sur un imprime propre, pas sur un carton
        // abime.
        $matrice = Encoder::encode($donnees, ErrorCorrectionLevel::L(), Encoder::DEFAULT_BYTE_MODE_ECODING)
            ->getMatrix();

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
