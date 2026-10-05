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
    public function __construct(private readonly ImageManager $images, private readonly CodeQr $qr) {}

    public const PAGES_GRATUITE = 6;

    public const PAGES_PAYANTE = 60;

    public const PAGES_DEVELOPPEMENT = 8;

    /** A4 portrait, en mm. */
    private const LARGEUR = 210;

    private const HAUTEUR = 297;

    /** Zone utile des visuels, en mm : sous le titre, au-dessus du pied. */
    private const ZONE = ['x' => 15, 'y' => 24, 'l' => 180, 'h' => 245];

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
     * @param  bool  $qr  code QR du book en bas de la couverture
     */
    public function generer(User $creatif, bool $titres = true, bool $legendes = false, bool $proteges = false, bool $qr = false): string
    {
        $pdf = new class('P', 'mm', [self::LARGEUR, self::HAUTEUR]) extends FPDF
        {
            public string $piedDePage = '';

            public function Footer(): void
            {
                if ($this->PageNo() === 1) {
                    return;
                }

                $this->SetY(-13);
                $this->SetFont('Roboto', '', 6);
                $this->SetTextColor(90, 90, 90);
                $this->Cell(0, 4, $this->piedDePage, 0, 1, 'C');
                $this->Cell(0, 5, $this->PageNo().'/{nb}', 0, 0, 'C');
            }
        };

        $pdf->AddFont('Roboto', '', 'Roboto-Regular.json', resource_path('fonts').'/');
        $pdf->piedDePage = $this->latin1($creatif->fullName().' / '.self::dateEnLettres());
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(false);
        $pdf->AliasNbPages();
        $pdf->SetTitle($this->latin1($creatif->fullName()));

        $this->couverture($pdf, $creatif, $qr);

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

    private function couverture(FPDF $pdf, User $creatif, bool $qr): void
    {
        $pdf->AddPage();

        // Visuel de profil en rond, au-dessus du nom.
        $profil = $creatif->bookSetting?->thumbnail;
        $y = 105;
        if ($profil && ($rond = $this->rond(DossierBook::chemin($creatif->login, basename($profil))))) {
            $pdf->Image($rond, (self::LARGEUR - 56) / 2, 62, 56, 56, 'PNG');
            @unlink($rond);
            $y = 135;
        }

        // Prenom et nom sur une seule ligne, reduits si elle deborde.
        $nom = $this->latin1(mb_strtoupper(trim($creatif->firstname.' '.($creatif->lastname ?: $creatif->login))));
        $taille = 24;
        $pdf->SetFont('Roboto', '', $taille);
        while ($taille > 14 && $pdf->GetStringWidth($nom) > self::LARGEUR - 30) {
            $pdf->SetFont('Roboto', '', --$taille);
        }

        $pdf->SetY($y);
        $pdf->Cell(0, 10, $nom, 0, 2, 'C');
        $pdf->Line(100, $y + 16, 110, $y + 16);
        $pdf->SetFont('Roboto', '', 10);
        $pdf->Ln(14);
        $pdf->Cell(0, 7, preg_replace('#^https?://#', '', $creatif->bookUrl()), 0, 2, 'C', false, $creatif->bookUrl());
        $pdf->Cell(0, 7, $this->latin1(self::dateEnLettres()), 0, 2, 'C');

        if ($qr && ($fichier = $this->fichierQr($creatif->bookUrl()))) {
            $pdf->Image($fichier, (self::LARGEUR - 28) / 2, self::HAUTEUR - 28 - 32, 28, 28, 'PNG');
            @unlink($fichier);
        }
    }

    /** « 5 juin 2026 », quelle que soit la langue de l'application. */
    private static function dateEnLettres(): string
    {
        return now()->locale('fr')->translatedFormat('j F Y');
    }

    /** PNG temporaire du code QR de l'adresse donnee. */
    private function fichierQr(string $adresse): ?string
    {
        $fichier = tempnam(sys_get_temp_dir(), 'pdfqr').'.png';

        return @file_put_contents($fichier, base64_decode($this->qr->pngBase64($adresse))) ? $fichier : null;
    }

    /**
     * Visuel de profil decoupe en rond (PNG temporaire) : recadre en carre,
     * puis les coins hors du cercle passent au blanc de la page, avec un bord
     * adouci d'un pixel. Null si le fichier est inutilisable.
     */
    private function rond(string $fichier): ?string
    {
        if (! ($carre = $this->jpeg($fichier, carre: true))) {
            return null;
        }

        $source = @imagecreatefromjpeg($carre);
        @unlink($carre);
        if (! $source) {
            return null;
        }

        $d = 600;
        $image = imagecreatetruecolor($d, $d);
        imagecopyresampled($image, $source, 0, 0, 0, 0, $d, $d, imagesx($source), imagesy($source));
        imagedestroy($source);

        $r = $d / 2;
        for ($py = 0; $py < $d; $py++) {
            for ($px = 0; $px < $d; $px++) {
                // Part du pixel hors du cercle : 0 dedans, 1 dehors, degrade sur 1 px.
                $dehors = max(0, min(1, hypot($px + .5 - $r, $py + .5 - $r) - $r + .5));
                if ($dehors <= 0) {
                    continue;
                }
                $c = imagecolorat($image, $px, $py);
                $m = fn (int $v) => (int) round($v + (255 - $v) * $dehors);
                imagesetpixel($image, $px, $py, imagecolorallocate($image, $m(($c >> 16) & 255), $m(($c >> 8) & 255), $m($c & 255)));
            }
        }

        $png = tempnam(sys_get_temp_dir(), 'pdfrond').'.png';
        imagepng($image, $png);
        imagedestroy($image);

        return $png;
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
            $pdf->SetFont('Roboto', '', 9);
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
                $pdf->SetFont('Roboto', '', 7.5);
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
