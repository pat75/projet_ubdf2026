<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Support\Marque;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CmsController extends Controller
{
    /** Pages affichees par la navigation laterale de la documentation. */
    private const RACINE_DOC = 'doc';

    /** Pages qui affichent la grille tarifaire, sous leur texte. */
    private const PAGES_TARIFS = ['les-formules-ultra-book', 'formules'];

    /**
     * Pages retirees (depubliees en base) => page de destination. Leurs URL
     * sont indexees : on les redirige plutot que de servir une 404.
     */
    private const RETIREES = [
        'qui-sommes-nous' => 'doc',
        'tuto-video-ultra-book' => 'doc',
        'tutos-videos' => 'doc',
    ];

    /**
     * Page editoriale : /doc/<slug> et /page__<slug>.
     *
     * Le legacy distinguait les deux : `/doc/x` etait traduit en
     * `pagename=/doc/x` (page fille de l'arbre de documentation) et
     * `/page__x` en `pagename=/x` (page de premier niveau). La distinction
     * n'a plus lieu d'etre — un slug est unique par langue — mais les deux
     * URL restent servies, elles sont indexees.
     */
    public function page(Request $request, string $slug): View|RedirectResponse
    {
        if (isset(self::RETIREES[$slug])) {
            return redirect()->to(lien('cms.doc', self::RETIREES[$slug]), 301);
        }


        $page = $this->trouver($slug);

        // Page d'une autre langue (ex. /doc/formules, anglaise, sur Ultra-book
        // qui ne sert que le francais) : 301 vers sa traduction, faute de quoi
        // le moteur indexe une page anglaise declaree lang="fr".
        if ($page->locale !== $this->locale()) {
            $traduction = $page->translation_group
                ? CmsPage::publiees()->where('translation_group', $page->translation_group)
                    ->where('locale', $this->locale())->value('slug')
                : null;

            return redirect()->to(lien('cms.doc', $traduction ?? self::RACINE_DOC), 301);
        }

        $this->adapterALaMarque($request, $page);

        return view('front.cms.page', [
            'page' => $page,
            'navigation' => $this->navigation($page),
            'tarifs' => in_array($page->slug, self::PAGES_TARIFS, true) ? $this->tarifs() : null,
        ]);
    }

    /**
     * Liste des actualites : /actus.
     *
     * Les 74 actualites reprises de WordPress sont toutes en francais. Un
     * visiteur anglophone verrait donc une page vide si la langue filtrait
     * strictement : a defaut d'actualites dans sa langue, on lui sert
     * celles de la langue par defaut plutot que rien.
     */
    public function actualites(): View
    {
        $locale = $this->locale();

        $existe = CmsPost::publiees()->where('locale', $locale)->exists();

        return view('front.cms.actualites', [
            'actualites' => CmsPost::publiees()
                ->where('locale', $existe ? $locale : config('langues.defaut'))
                ->orderByDesc('published_at')
                ->paginate(12),
        ]);
    }

    /** Une actualite : /actus/<slug>. */
    public function actualite(Request $request, string $slug): View|RedirectResponse
    {
        $actualite = CmsPost::publiees()->where('slug', $slug)->first();

        if (! $actualite) {
            // Ancienne adresse au slug encode par WordPress, decode par le
            // routeur (« d’illustrateurs ») : redirigee vers le slug nettoye.
            $propre = Str::slug($slug);

            if ($propre !== $slug && CmsPost::publiees()->where('slug', $propre)->exists()) {
                return redirect()->to(lien('actualite', $propre), 301);
            }

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
     * Grille publique : celle d'un nouveau createur, sans promotion (elles
     * dependent du compte) ni offre retiree de la vente en ligne. Le tarif
     * de reabonnement est donne a part. Tout vient de config/formules.php,
     * comme dans l'espace : les prix affiches ne peuvent pas diverger.
     *
     * @return array{options: array<int, array<string, mixed>>, reabonnement: ?array<string, mixed>, limites: array<string, array<string, int>>}
     */
    private function tarifs(): array
    {
        $grille = collect(config('formules.options'))
            ->reject(fn ($o) => isset($o['promo']) || ($o['en_ligne'] ?? true) === false);

        return [
            'options' => $grille->reject(fn ($o) => ! empty($o['reabonnement']))->all(),
            'reabonnement' => $grille->first(fn ($o) => ! empty($o['reabonnement'])),
            'limites' => config('formules.limites'),
        ];
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

        // La page racine (/doc/doc) ouvre le sommaire de ses propres filles.
        return CmsPage::publiees()
            ->where('locale', $page->locale)
            ->where(fn ($q) => $q->where('parent_slug', $parent)->orWhere('slug', $parent))
            ->orderByRaw('slug = ? DESC', [$parent])
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
