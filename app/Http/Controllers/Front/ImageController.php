<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Pages issues de l'analyse IA des visuels.
 *
 * - /images/{slug} : un mot-cle, ses images et les books qui les portent.
 *   Indexee seulement au-dela de Tag::INDEXABLE_* (contenu assez riche
 *   et varie) ; c'est elle qui vise les requetes de longue traine.
 * - /image/{id}/{slug} : le detail d'un visuel, pour le visiteur venu de
 *   la recherche. `noindex, follow` : une page par image serait du
 *   contenu mince produit en masse, et doublonnerait le book.
 */
class ImageController extends Controller
{
    public const IMAGES_PAR_MOTCLE = 48;

    public function show(Request $request, int $id, ?string $slug = null): View|RedirectResponse
    {
        $media = Media::visiblesSurPortail($this->brand($request))
            ->with(['user.bookSetting', 'user.category', 'tags' => fn ($q) => $q->where('lang', Tag::langueCourante())])
            ->findOrFail($id);

        if ($slug !== Str::slug($media->ai_title ?: 'image')) {
            return redirect()->to($media->pageUrl(), 301);
        }

        return view('front.image', [
            'media' => $media,
            'creatif' => $media->user,
            'autres' => Media::visiblesSurPortail($this->brand($request))
                ->where('media.user_id', $media->user_id)->whereKeyNot($media->id)
                ->latest('analysed_at')->limit(6)->get(),
        ]);
    }

    public function motCle(Request $request, string $slug): View
    {
        $tags = Tag::where('slug', $slug)->where('lang', Tag::langueCourante())->get();
        abort_if($tags->isEmpty(), 404);

        $images = Media::visiblesSurPortail($this->brand($request))
            ->whereHas('tags', fn ($query) => $query->whereKey($tags->modelKeys()))
            ->with(['user.category', 'user.bookSetting'])
            ->latest('analysed_at')
            ->limit(self::IMAGES_PAR_MOTCLE)
            ->get();

        abort_if($images->isEmpty(), 404);

        $creatifs = $images->pluck('user')->unique('id')->values();

        return view('front.images-motcle', [
            'motCle' => $tags->first()->label,
            'images' => $images,
            'creatifs' => $creatifs,
            'indexable' => $images->count() >= Tag::INDEXABLE_IMAGES && $creatifs->count() >= Tag::INDEXABLE_CREATIFS,
        ]);
    }

    private function brand(Request $request): string
    {
        return (string) $request->attributes->get('brand', 'ub');
    }
}
