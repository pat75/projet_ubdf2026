<?php

namespace App\Services\Espace;

use App\Models\BookSetting;
use App\Support\DossierBook;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

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
        $this->enregistrer($reglages, $fichier->getRealPath());
    }

    /**
     * Photo du compte Google, a la creation d'un book par Google. Seul
     * l'hote des photos Google est accepte : l'adresse vient d'un tiers.
     * Sans importance si elle echoue : le createur garde l'avatar par defaut.
     */
    public function depuisGoogle(BookSetting $reglages, ?string $url): void
    {
        if (! $url || ! preg_match('#^https://[a-z0-9-]+\.googleusercontent\.com/#', $url)) {
            return;
        }

        // =s96-c par defaut : on demande la photo en grand.
        $url = preg_replace('/=s\d+(-c)?$/', '=s'.self::COTE.'-c', $url);
        $temp = tempnam(sys_get_temp_dir(), 'avatar');

        try {
            $reponse = Http::timeout(10)->sink($temp)->get($url);
            if ($reponse->successful()) {
                $this->enregistrer($reglages, $temp);
            }
        } catch (Throwable) {
            // Photo facultative.
        } finally {
            @unlink($temp);
        }
    }

    private function enregistrer(BookSetting $reglages, string $source): void
    {
        $taille = @getimagesize($source);

        if ($taille === false || $taille[0] * $taille[1] > config('images.pixels_max')) {
            throw new RuntimeException(__('Cette image est illisible ou trop grande.'));
        }

        $creatif = $reglages->user;
        $ancien = $reglages->thumbnail;
        $nom = Str::lower(Str::random(32)).'.jpg';
        $chemin = DossierBook::chemin($creatif->login, $nom);

        File::ensureDirectoryExists(dirname($chemin));

        $this->images->decodePath($source)
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
