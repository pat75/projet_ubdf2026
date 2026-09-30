<?php

namespace App\Services\Espace;

use App\Models\Media;
use App\Models\User;
use App\Support\DossierBook;
use FPDF;
use Intervention\Image\ImageManager;

/**
 * PDF du book (reprise de 2011_html_pages_v2/pdf/ultrabook_pdf_mdl_carre.php).
 *
 * Pages carrees de 210 mm : une couverture (visuel de profil, prenom, nom,
 * adresse du book, date), puis les galeries dans l'ordre du book. Les
 * visuels de chaque galerie gardent l'ordre du portfolio et sont repartis
 * en pages selon leur format (MiseEnPagePdf) ; le nom de la galerie figure
 * en tete de chacune de ses pages.
 *
 * Nombre de pages de visuels limite (couverture non comprise) :
 * formule gratuite 6, formule payante 80, et 8 en developpement.
 */
class PdfBook
{
    public function __construct(private readonly ImageManager $images) {}

    public const PAGES_GRATUITE = 6;

    public const PAGES_PAYANTE = 60;

    public const PAGES_DEVELOPPEMENT = 8;

    private const COTE = 210;

    /** Zone utile des visuels, en mm : sous le titre, au-dessus du pied. */
    private const ZONE = ['x' => 15, 'y' => 24, 'l' => 180, 'h' => 163];

    /** Hauteur reservee au titre sous un visuel, en mm. */
    private const LEGENDE = 6;

    /** Plus grand cote d'un visuel reencode : de quoi imprimer, sans alourdir. */
    private const PIXELS_MAX = 2200;

    public static function pagesMax(User $creatif): int
    {
        if (app()->environment('local', 'development')) {
            return self::PAGES_DEVELOPPEMENT;
        }

        return $creatif->plan ? self::PAGES_PAYANTE : self::PAGES_GRATUITE;
    }

    /**
     * @param  bool  $titres  nom de la rubrique en tete de ses pages
     * @param  bool  $legendes  titre de chaque visuel sous l'image
     * @param  bool  $proteges  inclure les portfolios proteges par mot de passe
     */
    public function generer(User $creatif, bool $titres = true, bool $legendes = false, bool $proteges = false): string
    {
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
                $this->SetTextColor(90, 90, 90);
                $this->Cell(0, 4, $this->piedDePage, 0, 1, 'C');
                $this->Cell(0, 5, $this->PageNo().'/{nb}', 0, 0, 'C');
            }
        };

        $pdf->piedDePage = $this->latin1($creatif->fullName().' / '.now()->format('j.m.Y'));
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(false);
        $pdf->AliasNbPages();
        $pdf->SetTitle($this->latin1($creatif->fullName()));

        $this->couverture($pdf, $creatif);

        $restantes = self::pagesMax($creatif);
        $galeries = $creatif->galleries()->published()->whereNull('parent_id')->orderBy('position')
            // Un PDF circule : les portfolios proteges n'y entrent que sur demande.
            ->when(! $proteges, fn ($q) => $q->whereNull('password'))
            ->get();

        foreach ($galeries as $galerie) {
            if ($restantes <= 0) {
                break;
            }

            $ordre = array_flip($galerie->media_order ?? []);
            $visuels = $galerie->media()->published()->where('filename', '<>', '')->get()
                ->sortBy(fn (Media $m) => [$ordre[(string) ($m->legacy_id ?? $m->id)] ?? PHP_INT_MAX, $m->legacy_id ?? $m->id])
                ->map(fn (Media $m) => ['media' => $m, 'fichier' => DossierBook::chemin($creatif->login, $m->filename)])
                ->filter(fn (array $v) => $this->ratio($v) > 0)
                ->values()->all();

            foreach (MiseEnPagePdf::planifier($visuels, $this->ratio(...)) as $page) {
                if ($restantes <= 0) {
                    break;
                }
                if ($this->page($pdf, $titres ? $galerie->name : null, $page['gabarit'], $page['visuels'], $legendes)) {
                    $restantes--;
                }
            }
        }

        return $pdf->Output('S');
    }

    private function couverture(FPDF $pdf, User $creatif): void
    {
        $pdf->AddPage();

        // Visuel de profil, carre, au-dessus du nom.
        $profil = $creatif->bookSetting?->thumbnail;
        $y = 60;
        if ($profil && ($jpeg = $this->jpeg(DossierBook::chemin($creatif->login, basename($profil)), carre: true))) {
            $pdf->Image($jpeg, (self::COTE - 44) / 2, 34, 44, 44, 'JPG');
            @unlink($jpeg);
            $y = 88;
        }

        $pdf->SetY($y);
        $pdf->SetFont('Helvetica', '', 24);
        $pdf->Cell(0, 10, $this->latin1(mb_strtoupper((string) $creatif->firstname)), 0, 2, 'C');
        $pdf->Cell(0, 10, $this->latin1(mb_strtoupper((string) ($creatif->lastname ?: $creatif->login))), 0, 2, 'C');
        $pdf->Line(100, $y + 25, 110, $y + 25);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Ln(10);
        $pdf->Cell(0, 7, preg_replace('#^https?://#', '', $creatif->bookUrl()), 0, 2, 'C', false, $creatif->bookUrl());
        $pdf->Cell(0, 7, now()->format('j.m.Y'), 0, 2, 'C');
    }

    /**
     * Une page du gabarit donne ; faux si aucun visuel n'a pu y etre pose.
     *
     * @param  list<array{media: Media, fichier: string}>  $visuels
     */
    private function page(FPDF $pdf, ?string $galerie, string $gabarit, array $visuels, bool $legendes = false): bool
    {
        $cases = MiseEnPagePdf::cases($gabarit, self::ZONE['x'], self::ZONE['y'], self::ZONE['l'], self::ZONE['h']);
        $poses = [];

        foreach ($visuels as $i => $visuel) {
            [$x, $y, $l, $h, $carre] = $cases[$i];
            $legende = $legendes ? $this->legende($visuel['media']) : '';
            if ($jpeg = $this->jpeg($visuel['fichier'], $carre)) {
                $poses[] = [$jpeg, $x, $y, $l, $h, $carre, $legende];
            }
        }

        if ($poses === []) {
            return false;
        }

        $pdf->AddPage();

        // Nom du portfolio en tete de page, si le createur l'a garde.
        if ($galerie !== null) {
            $pdf->SetXY(self::ZONE['x'], 11);
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->Cell(self::ZONE['l'], 6, $this->latin1(mb_strtoupper($galerie)), 0, 0, 'L');
            $pdf->SetTextColor(0, 0, 0);
        }

        foreach ($poses as [$jpeg, $x, $y, $l, $h, $carre, $legende]) {
            // Le titre du visuel prend une ligne sous l'image, dans sa case.
            $hImage = $legende !== '' ? $h - self::LEGENDE : $h;

            [$largeur, $hauteur] = getimagesize($jpeg);
            // Contenu dans sa case et centre ; un carre la remplit.
            $echelle = $carre ? min($l, $hImage) / $largeur : min($l / $largeur, $hImage / $hauteur);
            $pl = $largeur * $echelle;
            $ph = $hauteur * $echelle;
            $py = $y + ($hImage - $ph) / 2;
            $pdf->Image($jpeg, $x + ($l - $pl) / 2, $py, $pl, $ph, 'JPG');
            @unlink($jpeg);

            if ($legende !== '') {
                $pdf->SetXY($x, $py + $ph + 1.5);
                $pdf->SetFont('Helvetica', '', 7.5);
                $pdf->SetTextColor(70, 70, 70);
                $pdf->Cell($l, 4, $this->couper($pdf, $this->latin1($legende), $l), 0, 0, 'C');
                $pdf->SetTextColor(0, 0, 0);
            }
        }

        return true;
    }

    /** @param  array{media: Media, fichier: string}  $visuel */
    private function ratio(array $visuel): float
    {
        $m = $visuel['media'];
        if ($m->width > 0 && $m->height > 0) {
            return $m->width / $m->height;
        }

        $taille = is_file($visuel['fichier']) ? @getimagesize($visuel['fichier']) : false;

        return $taille && $taille[1] > 0 ? $taille[0] / $taille[1] : 0.0;
    }

    /**
     * Copie JPEG temporaire du visuel (FPDF ne lit ni les PNG entrelaces ou
     * a 16 bits, ni la transparence), reduite a PIXELS_MAX, recadree en
     * carre au besoin. Null si le fichier est inutilisable.
     */
    private function jpeg(string $fichier, bool $carre = false): ?string
    {
        if (! is_file($fichier) || ! ($taille = @getimagesize($fichier))
            || $taille[0] * $taille[1] > config('images.pixels_max')) {
            return null;
        }

        $base = tempnam(sys_get_temp_dir(), 'pdfbook');
        $jpeg = $base.'.jpg';
        @unlink($base);

        try {
            $image = $this->images->decodePath($fichier)->orient();
            if ($carre) {
                $cote = min($image->width(), $image->height(), self::PIXELS_MAX);
                $image->cover($cote, $cote);
            } else {
                $image->scaleDown(self::PIXELS_MAX, self::PIXELS_MAX);
            }
            $image->save($jpeg, quality: 85);
        } catch (\Throwable) {
            @unlink($jpeg);

            return null;
        }

        return $jpeg;
    }

    /**
     * Titre du visuel a imprimer sous l'image, ou '' : un titre qui n'est
     * que le nom du fichier depose (« affiche.jpg ») n'apprend rien au
     * lecteur et n'est pas repris.
     */
    private function legende(Media $media): string
    {
        $titre = trim(html_entity_decode((string) $media->title, ENT_QUOTES, 'UTF-8'));

        $nomDeFichier = preg_match('/\.(jpe?g|gif|png|webp|avif|bmp|tiff?|heic)$/i', $titre)
            || mb_strtolower($titre) === mb_strtolower(pathinfo((string) $media->filename, PATHINFO_FILENAME));

        return $nomDeFichier ? '' : $titre;
    }

    /** Tronque une legende a la largeur de sa case, points de suspension compris. */
    private function couper(FPDF $pdf, string $texte, float $largeur): string
    {
        if ($pdf->GetStringWidth($texte) <= $largeur) {
            return $texte;
        }
        while ($texte !== '' && $pdf->GetStringWidth($texte.'...') > $largeur) {
            $texte = substr($texte, 0, -1);
        }

        return rtrim($texte).'...';
    }

    private function latin1(string $texte): string
    {
        return mb_convert_encoding(html_entity_decode($texte, ENT_QUOTES, 'UTF-8'), 'ISO-8859-1', 'UTF-8');
    }
}
