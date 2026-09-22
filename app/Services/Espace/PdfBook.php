<?php

namespace App\Services\Espace;

use App\Models\Media;
use App\Models\User;
use FPDF;
use Intervention\Image\ImageManager;

/**
 * PDF du book (2011_html_pages_v2/pdf/ultrabook_pdf_mdl_carre.php).
 *
 * Meme mise en page que le legacy : pages carrees de 210 mm, une
 * couverture (prenom, nom, adresse du book, date) puis un visuel centre
 * par page, galerie apres galerie. Memes limites aussi : 4 galeries,
 * 10 visuels et 4 pages en formule gratuite ; 30, 50 et 80 sinon.
 */
class PdfBook
{
    public function __construct(private readonly ImageManager $images) {}

    private const COTE = 210;

    /** Zone utile des visuels, en mm (827x680 px a 96 dpi dans le legacy). */
    private const LARGEUR_MAX = 219;

    private const HAUTEUR_MAX = 180;

    public function generer(User $creatif): string
    {
        [$galeriesMax, $visuelsMax, $pagesMax] = $creatif->plan ? [30, 50, 80] : [4, 10, 4];

        $pdf = new class('P', 'mm', [self::COTE, self::COTE]) extends FPDF
        {
            public string $piedDePage = '';

            public function Footer(): void
            {
                if ($this->PageNo() === 1) {
                    return;
                }

                $this->SetY(-13);
                $this->SetFont('Helvetica', '', 6);
                $this->Cell(0, 4, $this->piedDePage, 0, 1, 'C');
                $this->Cell(0, 5, $this->PageNo().'/{nb}', 0, 0, 'C');
            }
        };

        $pdf->piedDePage = $this->latin1($creatif->fullName().' / '.now()->format('j.m.Y'));
        $pdf->SetMargins(20, 20, 20);
        $pdf->AliasNbPages();
        $pdf->SetTitle($this->latin1($creatif->fullName()));

        $this->couverture($pdf, $creatif);

        $pages = 0;
        $galeries = $creatif->galleries()->published()->whereNull('parent_id')->orderBy('position')->limit($galeriesMax)->get();

        foreach ($galeries as $galerie) {
            $ordre = array_flip($galerie->media_order ?? []);
            $visuels = $galerie->media()->published()->where('filename', '<>', '')->get()
                ->sortBy(fn (Media $m) => [$ordre[(string) ($m->legacy_id ?? $m->id)] ?? PHP_INT_MAX, $m->legacy_id ?? $m->id])
                ->take($visuelsMax);

            foreach ($visuels as $visuel) {
                if ($pages >= $pagesMax) {
                    break 2;
                }

                if ($this->pageVisuel($pdf, storage_path('app/public/books/'.$creatif->login.'/'.$visuel->filename))) {
                    $pages++;
                }
            }
        }

        return $pdf->Output('S');
    }

    private function couverture(FPDF $pdf, User $creatif): void
    {
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 24);
        $pdf->Ln(60);
        $pdf->Cell(0, 10, $this->latin1(mb_strtoupper((string) $creatif->firstname)), 0, 2, 'C');
        $pdf->Cell(0, 10, $this->latin1(mb_strtoupper((string) ($creatif->lastname ?: $creatif->login))), 0, 2, 'C');
        $pdf->Line(100, 105, 110, 105);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Ln(10);
        $pdf->Cell(0, 7, preg_replace('#^https?://#', '', $creatif->bookUrl()), 0, 2, 'C', false, $creatif->bookUrl());
        $pdf->Cell(0, 7, now()->format('j.m.Y'), 0, 2, 'C');
    }

    /**
     * Ajoute une page centree sur le visuel ; faux si le fichier est
     * inutilisable. Le visuel est d'abord reencode en JPEG : FPDF ne lit ni
     * les PNG entrelaces ou a 16 bits, ni la transparence.
     */
    private function pageVisuel(FPDF $pdf, string $fichier): bool
    {
        if (! is_file($fichier) || ! ($taille = @getimagesize($fichier))
            || $taille[0] * $taille[1] > config('images.pixels_max')) {
            return false;
        }

        $base = tempnam(sys_get_temp_dir(), 'pdfbook');
        $jpeg = $base.'.jpg';
        @unlink($base);

        try {
            $image = $this->images->decodePath($fichier);
            $image->save($jpeg, quality: 85);
        } catch (\Throwable) {
            @unlink($jpeg);

            return false;
        }

        [$largeur, $hauteur] = [$image->width(), $image->height()];
        $echelle = min(self::LARGEUR_MAX / $largeur, self::HAUTEUR_MAX / $hauteur, (self::COTE - 10) / $largeur);
        $l = $largeur * $echelle;
        $h = $hauteur * $echelle;

        // Centre sur 210 x 180, comme le legacy : le pied de page garde sa place.
        $pdf->AddPage();
        $pdf->Image($jpeg, (self::COTE - $l) / 2, (180 - $h) / 2, $l, $h, 'JPG');
        @unlink($jpeg);

        return true;
    }

    private function latin1(string $texte): string
    {
        return mb_convert_encoding(html_entity_decode($texte, ENT_QUOTES, 'UTF-8'), 'ISO-8859-1', 'UTF-8');
    }
}
