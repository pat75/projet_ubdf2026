<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Support\Marque;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CmsController extends Controller
{
    /** Pages affichees par la navigation laterale de la documentation. */
    private const RACINE_DOC = 'doc';

    /**
     * Page editoriale : /doc/<slug> et /page__<slug>.
     *
     * Le legacy distinguait les deux : `/doc/x` etait traduit en
     * `pagename=/doc/x` (page fille de l'arbre de documentation) et
     * `/page__x` en `pagename=/x` (page de premier niveau). La distinction
     * n'a plus lieu d'etre — un slug est unique par langue — mais les deux
     * URL restent servies, elles sont indexees.
     */
    public function page(Request $request, string $slug): View
    {
        $page = $this->trouver($slug);
        $this->adapterALaMarque($request, $page);

        return view('front.cms.page', [
            'page' => $page,
            'navigation' => $this->navigation($page),
        ]);
    }

    /** Liste des actualites : /actus. */
    public function actualites(): View
    {
        return view('front.cms.actualites', [
            'actualites' => CmsPost::publiees()
                ->where('locale', $this->locale())
                ->orderByDesc('published_at')
                ->paginate(12),
        ]);
    }

    /** Une actualite : /actus/<slug>. */
    public function actualite(Request $request, string $slug): View
    {
        $actualite = CmsPost::publiees()->where('slug', $slug)->first();

        if (! $actualite) {
            throw new NotFoundHttpException;
        }

        $this->adapterALaMarque($request, $actualite);

        return view('front.cms.actualite', [
            'actualite' => $actualite,
            'suivantes' => CmsPost::publiees()
                ->where('locale', $actualite->locale)
                ->whereKeyNot($actualite->getKey())
                ->orderByDesc('published_at')
                ->limit(4)
                ->get(),
        ]);
    }

    /**
     * Cherche la page dans la langue courante, puis dans les autres.
     *
     * Une page anglaise reste accessible par son slug meme quand le portail
     * est en francais : les deux arbres cohabitent dans la meme table, et
     * ces URL sont indexees separement.
     */
    private function trouver(string $slug): CmsPage
    {
        $page = CmsPage::publiees()
            ->where('slug', $slug)
            ->orderByRaw('locale = ? DESC', [$this->locale()])
            ->first();

        if (! $page) {
            throw new NotFoundHttpException;
        }

        return $page;
    }

    /**
     * Pages soeurs, pour le sommaire lateral.
     *
     * @return \Illuminate\Support\Collection<int, CmsPage>
     */
    private function navigation(CmsPage $page): \Illuminate\Support\Collection
    {
        $parent = $page->parent_slug ?: self::RACINE_DOC;

        return CmsPage::publiees()
            ->where('locale', $page->locale)
            ->where('parent_slug', $parent)
            ->orderBy('position')
            ->orderBy('title')
            ->get();
    }

    /**
     * Substitue le nom de la marque dans un contenu editorial.
     *
     * Dustfolio n'a jamais eu de pages a lui : le legacy servait celles
     * d'Ultra-book en y remplacant le nom au vol, juste avant l'affichage.
     * Le contenu stocke reste donc celui d'Ultra-book — c'est la seule
     * facon de ne pas dupliquer 27 pages pour 388 comptes.
     */
    private function adapterALaMarque(Request $request, CmsPage|CmsPost $contenu): void
    {
        $marque = $request->attributes->get('marque') ?? Marque::defaut();

        if ($marque->estDefaut()) {
            return;
        }

        $contenu->title = $marque->adapter($contenu->title);
        $contenu->body = $marque->adapter($contenu->body);
        $contenu->excerpt = $marque->adapter($contenu->excerpt);
    }

    private function locale(): string
    {
        return substr(app()->getLocale(), 0, 2);
    }
}
