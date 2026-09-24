<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Models\PageImage;
use App\Services\Espace\DepotImagePage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Bibliotheque d'images du bouton image de l'editeur Redactor
 * (App\Livewire\Espace\Pages, resources/js/espace.js) : deposer une image
 * (glisser-deposer ou choix de fichier), la lister, la remplacer ou la
 * supprimer. Chaque action est scopee au createur connecte.
 */
class PageImageController extends Controller
{
    /** GET /espace/pages/images — la bibliotheque du createur, la plus recente d'abord. */
    public function index(): JsonResponse
    {
        $images = Auth::user()->pageImages()->latest()->get()->map(fn (PageImage $image) => [
            'id' => $image->id,
            'url' => $image->url(),
            'nom' => $image->original_name,
            'taille' => $image->size,
            'creeeLe' => $image->created_at->format('d/m/Y'),
        ]);

        return response()->json($images);
    }

    /**
     * POST /espace/pages/upload-image — deposee depuis l'editeur (glisser
     * ou choix de fichier direct) ou depuis le bouton « Ajouter » de la
     * bibliotheque. Reponse au format attendu par Redactor 3 : {"filelink": "..."}.
     */
    public function store(Request $request, DepotImagePage $depot): JsonResponse
    {
        $request->validate(['file' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:'.config('images.envoi_ko_max')]]);

        try {
            $image = $depot->deposer(Auth::user(), $request->file('file'));
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['filelink' => $image->url(), 'id' => $image->id]);
    }

    /** POST /espace/pages/images/{image} — remplace le fichier, l'URL ne change pas. */
    public function update(Request $request, PageImage $image, DepotImagePage $depot): JsonResponse
    {
        abort_unless($image->user_id === Auth::id(), 403);

        $request->validate(['file' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:'.config('images.envoi_ko_max')]]);

        try {
            $image = $depot->remplacer($image, $request->file('file'));
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['url' => $image->url()]);
    }

    /** DELETE /espace/pages/images/{image} */
    public function destroy(PageImage $image, DepotImagePage $depot): JsonResponse
    {
        abort_unless($image->user_id === Auth::id(), 403);

        $depot->supprimer($image);

        return response()->json(['ok' => true]);
    }
}
