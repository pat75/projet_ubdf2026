<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Images\Declinaison;
use App\Services\Images\GenerateurImages;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Sert les visuels des books, dans la declinaison demandee.
 *
 * Remplace le lien symbolique vers storage pour trois raisons :
 *
 *   - la base reference des visuels dont le fichier a disparu du disque. Le
 *     legacy traitait le cas dans son .htaccess, en renvoyant une image par
 *     defaut plutot qu'une erreur. On fait la meme chose, sinon une carte
 *     affiche une icone cassee ;
 *   - c'est ici que se produisent les declinaisons, a la demande, la ou le
 *     legacy en pre-generait neuf par visuel a l'enregistrement ;
 *   - les noms de declinaison sont valides contre `config/images.php`, ce
 *     que phpThumb ne faisait pas : il prenait ses dimensions dans l'URL.
 */
class BookMediaController extends Controller
{
    public function __construct(private readonly GenerateurImages $generateur) {}

    /** `/books/{login}/{file}` — declinaison par defaut. */
    public function show(string $login, string $file): BinaryFileResponse|Response
    {
        return $this->servir($login, $file, null);
    }

    /**
     * `/books/{login}/{declinaison}/{file}` — declinaison nommee.
     *
     * Methode distincte, et non un troisieme argument optionnel de `show()`.
     * Laravel passe les parametres de route scalaires **dans l'ordre de
     * l'URI**, jamais par leur nom : une signature
     * `(login, file, declinaison)` recevait la declinaison dans `$file` et
     * le fichier dans `$declinaison`. La route s'appariait correctement et
     * le service produisait bien l'image — la permutation avait lieu entre
     * les deux, et se voyait seulement en HTTP reel.
     */
    public function showDeclinaison(string $login, string $declinaison, string $file): BinaryFileResponse|Response
    {
        return $this->servir($login, $file, $declinaison);
    }

    private function servir(string $login, string $file, ?string $declinaison): BinaryFileResponse|Response
    {
        // Ces segments viennent de l'URL : ils ne doivent pas permettre de
        // remonter l'arborescence.
        if (str_contains($file, '..') || str_contains($login, '..')) {
            abort(404);
        }

        $format = Declinaison::nommee($declinaison);

        if ($format === null) {
            abort(404);
        }

        $source = Storage::disk('public')->path('books/'.$login.'/'.$file);
        $produite = $this->generateur->produire($source, $format);

        if ($produite === null) {
            return $this->parDefaut();
        }

        return response()->file($produite, [
            // Un an : remplacer un visuel dans l'espace creatif produira une
            // nouvelle entree de cache, l'URL portant le nom du fichier.
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * `/books/{login}/cms/{chemin}` — images inserees dans les pages.
     *
     * Servies telles quelles, sans declinaison : elles sont deja a la
     * taille choisie par le createur dans l'editeur.
     */
    public function cms(string $login, string $chemin): BinaryFileResponse|Response
    {
        $racine = realpath(Storage::disk('public')->path('books/'.$login.'/img_cms'));
        $fichier = $racine ? realpath($racine.'/'.rawurldecode($chemin)) : false;

        // realpath() resout les « .. » : le fichier doit rester sous la racine.
        if (! $fichier || ! str_starts_with($fichier, $racine.DIRECTORY_SEPARATOR) || ! is_file($fichier)) {
            return $this->parDefaut();
        }

        return response()->file($fichier, ['Cache-Control' => 'public, max-age=604800']);
    }

    /** Trame grise du legacy, affichee a la place d'un visuel manquant. */
    private function parDefaut(): BinaryFileResponse|Response
    {
        $defaut = public_path('img_default/ultra-book_default_trame_91x91.gif');

        return is_file($defaut)
            ? response()->file($defaut, ['Cache-Control' => 'public, max-age=604800'])
            : response('', 404);
    }
}
