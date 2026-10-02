<?php

namespace App\Services\Espace;

use App\Models\Gallery;
use App\Models\Media;
use App\Services\Images\Declinaison;
use App\Services\Images\GenerateurImages;
use App\Support\DossierBook;
use App\Support\VideoEnLigne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use RuntimeException;
use Throwable;

/**
 * Enregistre un visuel envoye depuis l'espace creatif.
 *
 * Comme le legacy, on ne garde que la source bornee (declinaison `source`,
 * 1980x3600) : les autres tailles naissent a la demande. Le fichier est
 * reencode, ce qui retire au passage les donnees EXIF et tout contenu
 * greffe a l'image.
 *
 * Une video YouTube ou Vimeo est un visuel comme un autre : sa vignette est
 * stockee de la meme facon, et `video_url` dit au book d'ouvrir le lecteur
 * au clic. Elle compte donc dans le quota de visuels.
 *
 * Une fois la reponse partie, les tailles des pages de book
 * (PRECHAUFFEES) sont fabriquees d'avance : le premier visiteur du book
 * ne les attend pas. Les autres naissent toujours a la demande.
 */
class DepotVisuel
{
    /** Tailles de toutes les pages de book (srcset 550 / 320). */
    public const PRECHAUFFEES = ['ptf_medium', 'iph_medium'];

    public function __construct(
        private readonly ImageManager $images,
        private readonly Quotas $quotas,
    ) {}

    public function deposer(Gallery $galerie, UploadedFile $fichier): Media
    {
        return $this->stocker($galerie, $fichier->getRealPath(), [
            'title' => pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME),
        ]);
    }

    public function deposerVideo(Gallery $galerie, string $lien): Media
    {
        $video = VideoEnLigne::depuis($lien)
            ?? throw new RuntimeException(__('Ce lien n’est pas une vidéo YouTube ou Vimeo.'));

        $infos = $this->oembed($video);
        $temporaire = tempnam(sys_get_temp_dir(), 'video');

        try {
            file_put_contents($temporaire, $this->vignette($video, $infos));

            return $this->stocker($galerie, $temporaire, [
                'title' => Str::limit((string) ($infos['title'] ?? $video->nomPlateforme()), 250, ''),
                'video_url' => $video->lien(),
            ], $this->boutonLecture(...));
        } finally {
            @unlink($temporaire);
        }
    }

    /**
     * Remplace l'image d'un visuel, en gardant sa place, ses textes et sa
     * video : pour une video, l'image deposee devient sa vignette.
     */
    public function remplacer(Media $visuel, UploadedFile $fichier): Media
    {
        $creatif = $visuel->user;
        $ecrit = $this->ecrire($creatif->login, $fichier->getRealPath(), $visuel->video_url ? $this->boutonLecture(...) : null);
        $ancien = (int) $visuel->size;

        $visuel->update($ecrit);
        $creatif->increment('storage_used', (int) $visuel->size - $ancien);

        return $visuel;
    }

    /**
     * @param  array<string, string>  $attributs
     * @param  (\Closure(ImageInterface): mixed)|null  $retouche
     */
    private function stocker(Gallery $galerie, string $source, array $attributs, ?\Closure $retouche = null): Media
    {
        $creatif = $galerie->user;
        $this->quotas->verifierAjout($creatif->fresh());
        $ecrit = $this->ecrire($creatif->login, $source, $retouche);

        return DB::transaction(function () use ($galerie, $creatif, $attributs, $ecrit) {
            $media = $galerie->media()->create($attributs + $ecrit + [
                'user_id' => $creatif->id,
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

    /**
     * Ecrit la source bornee dans le dossier du book.
     *
     * @param  (\Closure(ImageInterface): mixed)|null  $retouche
     * @return array{filename: string, mime: string, size: int, width: int, height: int}
     */
    private function ecrire(string $login, string $source, ?\Closure $retouche): array
    {
        $taille = @getimagesize($source);

        if ($taille === false || $taille[0] * $taille[1] > config('images.pixels_max')) {
            throw new RuntimeException(__('Cette image est illisible ou trop grande.'));
        }

        $declinaison = Declinaison::nommee('source');
        $extension = $taille['mime'] === 'image/png' ? 'png' : 'jpg';
        $nom = Str::lower(Str::random(32)).'.'.$extension;
        $chemin = DossierBook::chemin($login, $nom);

        File::ensureDirectoryExists(dirname($chemin));

        $image = $this->images->decodePath($source)
            ->orient()
            ->scaleDown($declinaison->largeur, $declinaison->hauteur);

        if ($retouche) {
            $retouche($image);
        }

        $extension === 'png'
            ? $image->save($chemin)
            : $image->save($chemin, quality: config('images.qualite'));

        $this->prechauffer($chemin);

        return [
            'filename' => $nom,
            'mime' => mime_content_type($chemin),
            'size' => filesize($chemin),
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }

    /**
     * Fabrique les tailles des pages de book apres l'envoi de la reponse,
     * dans le meme processus : pas de worker de file a faire tourner. Un
     * echec ne coute rien, la taille sera faite a la demande.
     */
    private function prechauffer(string $chemin): void
    {
        dispatch(function () use ($chemin) {
            $generateur = app(GenerateurImages::class);

            foreach (self::PRECHAUFFEES as $nom) {
                try {
                    $generateur->produire($chemin, Declinaison::nommee($nom), webp: config('images.webp'));
                } catch (Throwable) {
                }
            }
        })->afterResponse();
    }

    /**
     * Titre et vignette annonces par la plateforme. Une video privee ou
     * supprimee n'a pas de fiche oEmbed : on la refuse.
     *
     * @return array<string, mixed>
     */
    private function oembed(VideoEnLigne $video): array
    {
        $point = $video->plateforme === 'youtube'
            ? 'https://www.youtube.com/oembed'
            : 'https://vimeo.com/api/oembed.json';

        try {
            $reponse = Http::timeout(8)->get($point, ['url' => $video->lien(), 'format' => 'json', 'width' => 1280]);
        } catch (Throwable) {
            throw new RuntimeException(__(':plateforme ne répond pas, réessayez dans un instant.', ['plateforme' => $video->nomPlateforme()]));
        }

        if (! $reponse->successful() || ! is_array($reponse->json())) {
            throw new RuntimeException(__('Vidéo introuvable ou privée sur :plateforme.', ['plateforme' => $video->nomPlateforme()]));
        }

        return $reponse->json();
    }

    /**
     * Bouton lecture dessine dans la vignette meme : les onze habillages du
     * book l'affichent ainsi sans retouche de gabarit, quel que soit leur
     * cadrage (mosaique, diaporama, visionneuse).
     */
    private function boutonLecture(ImageInterface $image): void
    {
        $x = intdiv($image->width(), 2);
        $y = intdiv($image->height(), 2);
        $r = max(18, intdiv(min($image->width(), $image->height()), 7));
        $t = (int) round($r * .45);

        $image->drawCircle(fn ($c) => $c->at($x, $y)->radius($r)->background('rgba(0, 0, 0, .6)'));
        $image->drawPolygon(fn ($p) => $p
            ->point($x - (int) round($t * .7), $y - $t)
            ->point($x - (int) round($t * .7), $y + $t)
            ->point($x + $t, $y)
            ->background('#ffffff'));
    }

    /** L'image de la video, dans la plus grande taille disponible. */
    private function vignette(VideoEnLigne $video, array $infos): string
    {
        $adresses = $video->plateforme === 'youtube'
            ? ['https://i.ytimg.com/vi/'.$video->identifiant.'/maxresdefault.jpg', 'https://i.ytimg.com/vi/'.$video->identifiant.'/hqdefault.jpg']
            : [(string) ($infos['thumbnail_url'] ?? '')];

        foreach (array_filter($adresses) as $adresse) {
            try {
                $reponse = Http::timeout(8)->get($adresse);
            } catch (Throwable) {
                continue;
            }

            if ($reponse->successful() && $reponse->body() !== '') {
                return $reponse->body();
            }
        }

        throw new RuntimeException(__('Impossible de récupérer l’image de cette vidéo.'));
    }
}
