<?php

namespace App\Services\Espace;

use App\Models\BookSetting;
use App\Support\DossierBook;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use RuntimeException;

/**
 * Enregistre la photo de profil du createur (bookSetting.thumbnail).
 *
 * L'image recue est deja recadree en carre cote navigateur (voir
 * recadrageAvatar dans resources/js/espace.js) : `cover` la borne malgre
 * tout a 1024x1024, au cas ou un appel direct de l'action contournerait le
 * recadrage. Meme pipeline que DepotVisuel — reencodage, retrait de l'EXIF
 * — mais ecrit directement le champ thumbnail plutot qu'un Media de galerie.
 */
class DepotAvatar
{
    private const COTE = 1024;

    public function __construct(private readonly ImageManager $images) {}

    public function deposer(BookSetting $reglages, UploadedFile $fichier): void
    {
        $taille = @getimagesize($fichier->getRealPath());

        if ($taille === false || $taille[0] * $taille[1] > config('images.pixels_max')) {
            throw new RuntimeException(__('Cette image est illisible ou trop grande.'));
        }

        $creatif = $reglages->user;
        $ancien = $reglages->thumbnail;
        $nom = Str::lower(Str::random(32)).'.jpg';
        $chemin = DossierBook::chemin($creatif->login, $nom);

        File::ensureDirectoryExists(dirname($chemin));

        $this->images->decodePath($fichier->getRealPath())
            ->orient()
            ->cover(self::COTE, self::COTE)
            ->save($chemin, quality: config('images.qualite'));

        $reglages->update(['thumbnail' => $nom]);

        $this->supprimerFichier($creatif->login, $ancien);
    }

    public function retirer(BookSetting $reglages): void
    {
        $ancien = $reglages->thumbnail;

        if (! $ancien) {
            return;
        }

        $reglages->update(['thumbnail' => null]);

        $this->supprimerFichier($reglages->user->login, $ancien);
    }

    private function supprimerFichier(string $login, ?string $fichier): void
    {
        if (! $fichier) {
            return;
        }

        $chemin = DossierBook::chemin($login, basename($fichier));

        if (is_file($chemin)) {
            File::delete($chemin);
        }
    }
}
