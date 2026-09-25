<?php

namespace App\Services\Espace;

use App\Models\PageImage;
use App\Models\User;
use App\Support\DossierBook;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use RuntimeException;

/**
 * Depose une image inseree dans le corps d'une page (editeur Redactor de
 * App\Livewire\Espace\Pages), dans cms/ du dossier du book
 * (books/a/d/o/adolie/cms/). Les images du legacy restent dans img_cms/ ;
 * les deux sont servies sous la meme URL /books/<login>/cms/...
 * (BookMediaController::cms) — telles quelles, a la taille choisie dans l'editeur, sans
 * declinaison a la volee. Ce ne sont pas des visuels de portfolio : pas
 * d'entree Media, elles ne comptent pas dans le quota de App\Services\Espace\Quotas ;
 * seule leur ligne App\Models\PageImage les rend listables et supprimables
 * depuis la bibliotheque du bouton image de l'editeur.
 */
class DepotImagePage
{
    /** Dossier des images deposees depuis l'editeur, dans le dossier du book. */
    public const DOSSIER = 'cms';

    /** Dossier des images reprises du legacy, lu en repli. */
    public const DOSSIER_LEGACY = 'img_cms';

    /** Fichier d'une image : cms/, ou img_cms/ pour une image deposee avant le changement de dossier. */
    public static function chemin(string $login, string $nom): string
    {
        $chemin = DossierBook::chemin($login, self::DOSSIER.'/'.$nom);
        $ancien = DossierBook::chemin($login, self::DOSSIER_LEGACY.'/'.$nom);

        return ! is_file($chemin) && is_file($ancien) ? $ancien : $chemin;
    }

    public function __construct(private readonly ImageManager $images) {}

    public function deposer(User $creatif, UploadedFile $fichier): PageImage
    {
        $ecrit = $this->ecrire($creatif->login, $fichier->getRealPath());

        return $creatif->pageImages()->create($ecrit + [
            'original_name' => pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME),
        ]);
    }

    /**
     * Remplace le fichier d'une image existante ; son nom, et donc son URL,
     * ne changent pas — sauf si le format change (png <-> jpg), auquel cas
     * l'ancien fichier est retire une fois le nouveau ecrit.
     */
    public function remplacer(PageImage $image, UploadedFile $fichier): PageImage
    {
        $login = $image->user->login;
        $ancien = $image->filename;
        $ecrit = $this->ecrire($login, $fichier->getRealPath(), $ancien);

        $image->update($ecrit);

        if ($ecrit['filename'] !== $ancien) {
            File::delete(self::chemin($login, $ancien));
        }

        return $image;
    }

    public function supprimer(PageImage $image): void
    {
        File::delete(self::chemin($image->user->login, $image->filename));
        $image->delete();
    }

    /**
     * @param  string|null  $nomExistant  reutilise si son extension convient (remplacement) ; sinon un nom est tire au sort.
     * @return array{filename: string, size: int, width: int, height: int}
     */
    private function ecrire(string $login, string $source, ?string $nomExistant = null): array
    {
        $taille = @getimagesize($source);

        if ($taille === false || $taille[0] * $taille[1] > config('images.pixels_max')) {
            throw new RuntimeException(__('Cette image est illisible ou trop grande.'));
        }

        $extension = $taille['mime'] === 'image/png' ? 'png' : 'jpg';
        $nom = $nomExistant && str_ends_with($nomExistant, '.'.$extension)
            ? $nomExistant
            : Str::lower(Str::random(32)).'.'.$extension;
        $chemin = DossierBook::chemin($login, self::DOSSIER.'/'.$nom);

        File::ensureDirectoryExists(dirname($chemin));

        // Bornee a la meme largeur que le corps de texte dans le book :
        // inutile de garder plus grand, l'editeur ne l'affichera jamais ainsi.
        $image = $this->images->decodePath($source)->orient()->scaleDown(1600, 3600);

        $extension === 'png'
            ? $image->save($chemin)
            : $image->save($chemin, quality: config('images.qualite'));

        return [
            'filename' => $nom,
            'size' => filesize($chemin),
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }
}
