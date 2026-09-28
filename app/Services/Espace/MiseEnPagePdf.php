<?php

namespace App\Services\Espace;

/**
 * Repartit les visuels d'une galerie en pages du PDF du book, selon leur
 * format, sans jamais changer leur ordre : on lit la file de gauche a
 * droite et chaque page prend les visuels qui la suivent.
 *
 * Quatre gabarits (cinq avec les deux variantes a deux images) :
 *
 * - QUATRE        quatre carres en grille 2 x 2 : quatre visuels sans paysage ;
 * - GRANDE_DEUX   un grand paysage en haut, deux petits visuels en bas ;
 * - SUPERPOSEES   deux visuels l'un sous l'autre, dont un paysage au moins ;
 * - COTE_A_COTE   deux visuels l'un a cote de l'autre, sans paysage (ou une
 *                 paire mixte portrait / paysage) ;
 * - PLEINE_PAGE   un visuel seul, en dernier recours.
 *
 * Pour chaque page, les gabarits possibles sont classes du plus rempli au
 * moins rempli ; on prend le premier qui ne repete pas la page precedente,
 * pour varier. Un visuel ne finit seul que s'il n'a pas de voisin.
 */
final class MiseEnPagePdf
{
    public const PLEINE_PAGE = 'pleine_page';

    public const COTE_A_COTE = 'cote_a_cote';

    public const SUPERPOSEES = 'superposees';

    public const GRANDE_DEUX = 'grande_deux';

    public const QUATRE = 'quatre';

    /** Au-dela de ce rapport largeur / hauteur, un visuel est un paysage. */
    private const PAYSAGE = 1.2;

    /** En deca, un portrait ; entre les deux, un carre. */
    private const PORTRAIT = 0.83;

    /**
     * @template T
     *
     * @param  list<T>  $visuels
     * @param  callable(T): float  $ratio  largeur / hauteur d'un visuel
     * @return list<array{gabarit: string, visuels: list<T>}>
     */
    public static function planifier(array $visuels, callable $ratio): array
    {
        $formats = array_map(fn ($v) => self::format($ratio($v)), $visuels);
        $pages = [];
        $i = 0;
        $n = count($visuels);

        $precedent = null;

        while ($i < $n) {
            $candidats = self::candidats(array_slice($formats, $i, 4));

            // Varier : le premier gabarit qui ne repete pas la page
            // precedente ; a defaut, le premier possible. La pleine page
            // ne vient qu'en dernier recours.
            [$gabarit, $nombre] = collect($candidats)->first(fn ($c) => $c[0] !== $precedent) ?? $candidats[0];
            if ($gabarit === self::PLEINE_PAGE && $candidats[0][0] !== self::PLEINE_PAGE) {
                [$gabarit, $nombre] = $candidats[0];
            }

            $pages[] = ['gabarit' => $gabarit, 'visuels' => array_slice($visuels, $i, $nombre)];
            $precedent = $gabarit;
            $i += $nombre;
        }

        return $pages;
    }

    /**
     * Gabarits possibles pour les visuels qui suivent (4 au plus), du plus
     * rempli au moins rempli ; la pleine page ferme toujours la liste.
     *
     * @param  list<string>  $f  formats des visuels a venir
     * @return list<array{0: string, 1: int}>
     */
    private static function candidats(array $f): array
    {
        $sansPaysage = fn (array $formats) => ! in_array('paysage', $formats, true);
        $candidats = [];

        // Quatre carres : les visuels sont recadres, un paysage y perdrait trop.
        if (count($f) === 4 && $sansPaysage($f)) {
            $candidats[] = [self::QUATRE, 4];
        }
        // Grand paysage en haut, deux petits visuels de tout format en bas.
        if (count($f) >= 3 && $f[0] === 'paysage') {
            $candidats[] = [self::GRANDE_DEUX, 3];
        }
        // L'un sous l'autre : au moins un paysage, aucun portrait.
        if (count($f) >= 2 && ! in_array('portrait', array_slice($f, 0, 2), true) && ! $sansPaysage(array_slice($f, 0, 2))) {
            $candidats[] = [self::SUPERPOSEES, 2];
        }
        // Cote a cote : aucun paysage.
        if (count($f) >= 2 && $sansPaysage(array_slice($f, 0, 2))) {
            $candidats[] = [self::COTE_A_COTE, 2];
        }

        // Paire mixte (portrait et paysage) : cote a cote plutot que deux
        // pleines pages.
        if (count($f) >= 2 && ! in_array([self::COTE_A_COTE, 2], $candidats, true)
            && ! in_array([self::SUPERPOSEES, 2], $candidats, true)) {
            $candidats[] = [self::COTE_A_COTE, 2];
        }

        $candidats[] = [self::PLEINE_PAGE, 1];

        return $candidats;
    }

    public static function format(float $ratio): string
    {
        return match (true) {
            $ratio > self::PAYSAGE => 'paysage',
            $ratio < self::PORTRAIT => 'portrait',
            default => 'carre',
        };
    }

    /**
     * Cases d'un gabarit dans la zone utile [x, y, largeur, hauteur], en mm.
     * La derniere valeur dit si le visuel est recadre en carre (QUATRE) ou
     * simplement contenu dans sa case.
     *
     * @return list<array{0: float, 1: float, 2: float, 3: float, 4: bool}>
     */
    public static function cases(string $gabarit, float $x, float $y, float $l, float $h, float $ecart = 5): array
    {
        $demiL = ($l - $ecart) / 2;
        $demiH = ($h - $ecart) / 2;

        return match ($gabarit) {
            self::COTE_A_COTE => [
                [$x, $y, $demiL, $h, false],
                [$x + $demiL + $ecart, $y, $demiL, $h, false],
            ],
            self::SUPERPOSEES => [
                [$x, $y, $l, $demiH, false],
                [$x, $y + $demiH + $ecart, $l, $demiH, false],
            ],
            self::GRANDE_DEUX => (function () use ($x, $y, $l, $h, $ecart, $demiL) {
                $haut = ($h - $ecart) * 0.58;
                $bas = $h - $ecart - $haut;

                return [
                    [$x, $y, $l, $haut, false],
                    [$x, $y + $haut + $ecart, $demiL, $bas, false],
                    [$x + $demiL + $ecart, $y + $haut + $ecart, $demiL, $bas, false],
                ];
            })(),
            self::QUATRE => (function () use ($x, $y, $l, $h, $ecart, $demiL, $demiH) {
                // Cases carrees, la grille centree dans la zone.
                $cote = min($demiL, $demiH);
                $x0 = $x + ($l - 2 * $cote - $ecart) / 2;
                $y0 = $y + ($h - 2 * $cote - $ecart) / 2;

                return [
                    [$x0, $y0, $cote, $cote, true],
                    [$x0 + $cote + $ecart, $y0, $cote, $cote, true],
                    [$x0, $y0 + $cote + $ecart, $cote, $cote, true],
                    [$x0 + $cote + $ecart, $y0 + $cote + $ecart, $cote, $cote, true],
                ];
            })(),
            default => [[$x, $y, $l, $h, false]],
        };
    }
}
