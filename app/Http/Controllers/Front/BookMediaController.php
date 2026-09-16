<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Sert les visuels des books.
 *
 * Remplace le lien symbolique vers storage pour deux raisons :
 *
 *   - la base reference des visuels dont le fichier a disparu du disque. Le
 *     legacy traitait le cas dans son .htaccess, en renvoyant une image par
 *     defaut plutot qu'une erreur. On fait la meme chose, sinon une carte
 *     affiche une icone cassee ;
 *   - c'est ici que s'accrochera la generation des declinaisons a la demande
 *     (phase 4), qui remplace les 12 dossiers pre-generes du legacy.
 */
class BookMediaController extends Controller
{
    public function show(string $login, string $file): BinaryFileResponse|Response
    {
        // Le nom de fichier vient de l'URL : il ne doit pas permettre de
        // remonter l'arborescence.
        if (str_contains($file, '..') || str_contains($login, '..')) {
            abort(404);
        }

        $path = Storage::disk('public')->path('books/'.$login.'/'.$file);

        if (! is_file($path)) {
            return $this->placeholder();
        }

        return response()->file($path, ['Cache-Control' => 'public, max-age=604800']);
    }

    /** Trame grise du legacy, affichee a la place d'un visuel manquant. */
    private function placeholder(): BinaryFileResponse|Response
    {
        $default = public_path('img_default/ultra-book_default_trame_91x91.gif');

        return is_file($default)
            ? response()->file($default, ['Cache-Control' => 'public, max-age=604800'])
            : response('', 404);
    }
}
