<?php

namespace App\Services\Espace;

use App\Models\Gallery;
use App\Models\Media;
use App\Services\Images\Declinaison;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use RuntimeException;

/**
 * Enregistre un visuel envoye depuis l'espace creatif.
 *
 * Comme le legacy, on ne garde que la source bornee (declinaison `source`,
 * 1980x3600) : les autres tailles naissent a la demande. Le fichier est
 * reencode, ce qui retire au passage les donnees EXIF et tout contenu
 * greffe a l'image.
 */
class DepotVisuel
{
    public function __construct(private readonly ImageManager $images) {}

    public function deposer(Gallery $galerie, UploadedFile $fichier): Media
    {
        $taille = @getimagesize($fichier->getRealPath());

        if ($taille === false || $taille[0] * $taille[1] > config('images.pixels_max')) {
            throw new RuntimeException(__('Cette image est illisible ou trop grande.'));
        }

        $creatif = $galerie->user;
        $source = Declinaison::nommee('source');
        $extension = $fichier->getMimeType() === 'image/png' ? 'png' : 'jpg';
        $nom = Str::lower(Str::random(32)).'.'.$extension;
        $chemin = storage_path('app/public/books/'.$creatif->login.'/'.$nom);

        File::ensureDirectoryExists(dirname($chemin));

        $image = $this->images->decodePath($fichier->getRealPath())
            ->orient()
            ->scaleDown($source->largeur, $source->hauteur);

        $extension === 'png'
            ? $image->save($chemin)
            : $image->save($chemin, quality: config('images.qualite'));

        return DB::transaction(function () use ($galerie, $creatif, $fichier, $nom, $chemin, $image) {
            $media = $galerie->media()->create([
                'user_id' => $creatif->id,
                'filename' => $nom,
                'title' => pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME),
                'mime' => mime_content_type($chemin),
                'size' => filesize($chemin),
                'width' => $image->width(),
                'height' => $image->height(),
                'status' => 'published',
                'position' => (int) $galerie->media()->max('position') + 1,
            ]);

            // Le nouveau visuel prend la derniere place de l'ordre du book.
            if ($galerie->media_order) {
                $galerie->update(['media_order' => [...$galerie->media_order, (string) $media->id]]);
            }

            $creatif->increment('media_count');
            $creatif->increment('storage_used', $media->size);

            return $media;
        });
    }
}
