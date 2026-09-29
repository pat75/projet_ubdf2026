<?php

namespace App\Services\Memo;

use App\Models\User;
use App\Models\Visitor;
use App\Services\Espace\AffichageProfil;
use App\Services\Espace\CodeQr;
use App\Services\Images\Declinaison;
use App\Services\Images\GenerateurImages;
use App\Support\DossierBook;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Intervention\Image\ImageManager;

/**
 * PDF du memoBook, sur le modele de la page web : groupes par annee et
 * par mois, une carte par book (couverture, vignette ronde, nom, metier,
 * code QR en option, adresse du book). Les images sont lues sur disque et
 * preparees ici : dompdf ne va pas les chercher sur le reseau.
 */
class PdfMemo
{
    public function __construct(
        private readonly MemoBooks $memo,
        private readonly GenerateurImages $images,
        private readonly CodeQr $qr,
        private readonly ImageManager $manager,
        private readonly AffichageProfil $profil,
    ) {}

    /** Couverture recadree au format de la case du PDF (86 x 48 mm). */
    private const COUVERTURE = [1000, 560];

    /**
     * `$codesQr` : ajoute sous chaque fiche le code QR de l'adresse du book.
     * `$titre` : titre du document (« La selection de … » en version publique).
     */
    public function generer(User|Visitor $proprietaire, bool $codesQr = false, ?string $titre = null): string
    {
        $carre = Declinaison::nommee('carre_183');

        $fiches = $this->memo->books($proprietaire)->map(function (User $book) use ($carre, $codesQr) {
            $couverture = $book->media->first();
            $fichier = $couverture ? DossierBook::chemin($book->login, $couverture->filename) : null;

            $vignette = $book->bookSetting?->thumbnail && $carre
                ? $this->images->produire(DossierBook::chemin($book->login, basename($book->bookSetting->thumbnail)), $carre)
                : null;

            return [
                'nom' => $book->fullName(),
                'metier' => $book->category?->name,
                'url' => $book->bookUrl(),
                'memorise_le' => Carbon::parse($book->memorise_le),
                'qr' => $codesQr ? 'data:image/png;base64,'.$this->qr->pngBase64($book->bookUrl()) : null,
                'image' => $fichier ? $this->couverture($fichier) : null,
                'avatar' => $vignette ? $this->rond($vignette) : null,
                'initiales' => $this->profil->initiales($book),
                'couleur' => $this->profil->couleur($book),
            ];
        });

        // L'auteur de la selection, en tete : un createur (photo ou
        // initiales, nom, metier). Un visiteur n'a ni nom ni avatar.
        $auteur = null;
        if ($proprietaire instanceof User) {
            $vignette = $proprietaire->bookSetting?->thumbnail && $carre
                ? $this->images->produire(DossierBook::chemin($proprietaire->login, basename($proprietaire->bookSetting->thumbnail)), $carre)
                : null;
            $auteur = [
                'nom' => $proprietaire->fullName(),
                'metier' => $proprietaire->category?->name,
                'avatar' => $vignette ? $this->rond($vignette) : null,
                'initiales' => $this->profil->initiales($proprietaire),
                'couleur' => $this->profil->couleur($proprietaire),
            ];
        }

        $pdf = Pdf::loadView('memo.pdf', ['fiches' => $fiches, 'codesQr' => $codesQr, 'titre' => $titre ?? __('mémoBook'), 'auteur' => $auteur])
            ->setPaper('a4');

        // Folio « 2 / 5 » centre en bas de chaque page : pose sur le
        // canevas une fois le rendu fait, quand le nombre de pages est connu.
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $canevas = $dompdf->getCanvas();
        $police = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $texte = '{PAGE_NUM} / {PAGE_COUNT}';
        $largeur = $dompdf->getFontMetrics()->getTextWidth('0 / 0', $police, 8);
        $canevas->page_text(($canevas->get_width() - $largeur) / 2, $canevas->get_height() - 24, $texte, $police, 8, [0.47, 0.47, 0.47]);

        return $pdf->output();
    }

    /**
     * Couverture a la largeur exacte de la fiche : dompdf ne connait pas
     * `object-fit`, l'image est donc recadree ici, comme `object-cover`.
     */
    private function couverture(string $fichier): ?string
    {
        try {
            if (! is_file($fichier)) {
                return null;
            }

            $jpeg = (string) $this->manager->decodePath($fichier)->cover(...self::COUVERTURE)->encodeUsingMediaType('image/jpeg', quality: 82);
        } catch (\Throwable) {
            return null;
        }

        return 'data:image/jpeg;base64,'.base64_encode($jpeg);
    }

    /** Vignette du createur decoupee en rond (PNG transparent) : dompdf ne rogne pas les images en cercle. */
    private function rond(string $fichier): ?string
    {
        try {
            $source = imagecreatefromstring((string) $this->manager->decodePath($fichier)->cover(240, 240)->encodeUsingMediaType('image/png'));
        } catch (\Throwable) {
            return null;
        }

        $cote = 240;
        $rond = imagecreatetruecolor($cote, $cote);
        imagesavealpha($rond, true);
        imagefill($rond, 0, 0, imagecolorallocatealpha($rond, 0, 0, 0, 127));

        for ($y = 0; $y < $cote; $y++) {
            for ($x = 0; $x < $cote; $x++) {
                if ((($x - $cote / 2 + .5) ** 2 + ($y - $cote / 2 + .5) ** 2) <= ($cote / 2) ** 2) {
                    imagesetpixel($rond, $x, $y, imagecolorat($source, $x, $y));
                }
            }
        }

        ob_start();
        imagepng($rond);
        imagedestroy($rond);
        imagedestroy($source);

        return 'data:image/png;base64,'.base64_encode((string) ob_get_clean());
    }
}
