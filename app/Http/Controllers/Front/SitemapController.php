<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CmsPage;
use App\Models\Media;
use App\Models\User;
use App\Support\Marque;
use App\Support\Metier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Sitemap du portail.
 *
 * Le legacy avait deux fichiers XML ecrits a la main (31 URL pour
 * Ultra-book, 13 pour Dustfolio) : aucun book n'y figurait. Ici les books
 * diffuses y sont, par paquets de 10 000, derriere un index.
 */
class SitemapController extends Controller
{
    /** Limite du protocole : 50 000 URL par fichier. On reste en deca. */
    private const PAR_FICHIER = 10000;

    private const CACHE_HEURES = 6;

    private const IMAGES_PAR_BOOK = 10;

    public function index(Request $requete): Response
    {
        $marque = $this->marque($requete);
        $total = Cache::remember('sitemap:books-total:'.$marque->code, now()->addHours(self::CACHE_HEURES),
            fn () => $this->booksQuery($marque)->count());
        $paquets = (int) ceil(max(1, $total) / self::PAR_FICHIER);

        $fichiers = [$this->url($marque, '/sitemap-pages.xml')];

        for ($i = 1; $i <= $paquets; $i++) {
            $fichiers[] = $this->url($marque, '/sitemap-books-'.$i.'.xml');
        }

        $images = Cache::remember('sitemap:images-total:'.$marque->code, now()->addHours(self::CACHE_HEURES),
            fn () => $this->imagesQuery($marque)->count());

        for ($i = 1; $i <= (int) ceil($images / self::PAR_FICHIER); $i++) {
            $fichiers[] = $this->url($marque, '/sitemap-images-'.$i.'.xml');
        }

        return $this->xml(view('sitemap.index', ['fichiers' => $fichiers]));
    }

    /**
     * Pages fixes : accueil, metiers, landings SEO, documentation, pages mot-cle.
     *
     * Une marque multilingue (Dustfolio) les liste dans chacune de ses
     * langues : la route n'a pas de segment de langue, et sans cette boucle
     * seules les pages de la langue par defaut etaient annoncees. Chaque
     * page porte ses versions (xhtml:link hreflang), comme dans le <head>.
     */
    public function pages(Request $requete): Response
    {
        $marque = $this->marque($requete);

        $xml = Cache::remember('sitemap:pages:'.$marque->code, now()->addHours(self::CACHE_HEURES),
            function () use ($marque) {
                $langueServie = app()->getLocale();
                $parLangue = [];

                try {
                    foreach ($marque->langues as $langue) {
                        app()->setLocale($langue);
                        $parLangue[$langue] = $this->pagesDeLaLangue($marque);
                    }
                } finally {
                    app()->setLocale($langueServie);
                }

                return view('sitemap.urls', ['urls' => $this->avecVersions($parLangue)])->render();
            });

        return $this->xml($xml);
    }

    /**
     * Pages de la langue courante, indexees par une cle commune a toutes les
     * langues (une page et sa traduction partagent la meme cle).
     *
     * @return array<string, array{loc: string, priorite: string}>
     */
    private function pagesDeLaLangue(Marque $marque): array
    {
        $langue = app()->getLocale();

        // L'accueil a pour adresse canonique la racine, pas /accueil.
        $urls = ['accueil' => ['loc' => rtrim($marque->canonique, '/').($marque->multilingue() ? '/'.$langue : '/'), 'priorite' => '1.00']];

        foreach (Category::where('is_active', true)->orderBy('position')->pluck('slug') as $slug) {
            $urls['metier:'.$slug] = ['loc' => $this->url($marque, '/'.Metier::slugUrl($slug)), 'priorite' => '0.80'];
        }

        // Landings : francaises, absentes de la version anglaise.
        foreach ($langue === 'fr' ? array_keys(config('seo_routes.landings')) : [] as $chemin) {
            $urls['landing:'.$chemin] = ['loc' => $this->url($marque, '/'.$chemin), 'priorite' => '0.60'];
        }

        // Pages dans la langue servie seulement, publiees seulement : une page
        // non publiee repond 404. Le groupe de traduction relie les versions.
        // Pages en noindex (copies d'Ultra-book, pages trop minces) : absentes.
        foreach (CmsPage::publiees()->pourMarque($marque->code)->where('locale', $langue)
            ->get(['slug', 'locale', 'marque', 'translation_group', 'body'])
            ->reject(fn (CmsPage $page) => $page->estUneCopiePour($marque) || $page->estMince())->unique('slug') as $page) {
            $cle = 'cms:'.($page->translation_group ?: $langue.':'.$page->slug);
            $urls[$cle] = ['loc' => $this->url($marque, '/doc/'.$page->slug), 'priorite' => '0.50'];
        }

        // Pages mot-cle de l'analyse IA, seulement celles assez riches pour etre indexees.
        foreach (Media::visiblesSurPortail($marque->code)
            ->join('media_tag', 'media_tag.media_id', '=', 'media.id')
            ->join('tags', 'tags.id', '=', 'media_tag.tag_id')
            ->where('tags.lang', \App\Models\Tag::langueCourante())
            ->groupBy('tags.slug')
            ->havingRaw('COUNT(DISTINCT media.id) >= ? AND COUNT(DISTINCT media.user_id) >= ?',
                [\App\Models\Tag::INDEXABLE_IMAGES, \App\Models\Tag::INDEXABLE_CREATIFS])
            ->pluck('tags.slug') as $slug) {
            $urls['mot:'.$langue.':'.$slug] = ['loc' => $this->url($marque, '/images/'.$slug), 'priorite' => '0.50'];
        }

        // Plus d'actualites : 2008-2013 pour l'essentiel (« Mozy », « IE »),
        // aucune n'est liee depuis le site. Elles restent en ligne.

        return $urls;
    }

    /**
     * Aplatit les pages de chaque langue ; une page presente dans plusieurs
     * langues recoit la liste de ses versions, x-default compris.
     *
     * @param  array<string, array<string, array{loc: string, priorite: string}>>  $parLangue
     */
    private function avecVersions(array $parLangue): array
    {
        $urls = [];

        foreach ($parLangue as $pages) {
            foreach ($pages as $cle => $page) {
                $versions = [];
                foreach ($parLangue as $langue => $autres) {
                    if (isset($autres[$cle])) {
                        $versions[$langue] = $autres[$cle]['loc'];
                    }
                }

                if (count($versions) > 1) {
                    $page['versions'] = $versions + ['x-default' => reset($versions)];
                }

                $urls[] = $page;
            }
        }

        return $urls;
    }

    /** Un paquet de books diffuses. */
    public function books(Request $requete, int $paquet): Response
    {
        $marque = $this->marque($requete);

        abort_if($paquet < 1 || $paquet > 1000, 404);

        $xml = Cache::remember('sitemap:books:'.$marque->code.':'.$paquet, now()->addHours(self::CACHE_HEURES),
            function () use ($marque, $paquet) {
                // lastmod : dernier visuel publie. users.updated_at porte la date
                // de l'import pour tous les comptes repris : signal sans valeur.
                $urls = $this->booksQuery($marque)
                    ->withMax(['media as dernier_visuel' => fn ($q) => $q->published()], 'created_at')
                    ->orderBy('id')
                    ->skip(($paquet - 1) * self::PAR_FICHIER)->take(self::PAR_FICHIER)
                    // Visuels pour Google Images : les 10 derniers de chaque book.
                    ->with(['media' => fn ($q) => $q->published()->horsProteges()->whereNot('filename', '')
                        ->latest('id')->limit(self::IMAGES_PAR_BOOK)])
                    ->get(['id', 'login', 'brand'])
                    ->map(fn (User $creatif) => [
                        'loc' => $creatif->bookUrl(),
                        'priorite' => '0.70',
                        'date' => $creatif->dernier_visuel ? \Illuminate\Support\Carbon::parse($creatif->dernier_visuel)->toAtomString() : null,
                        'images' => $creatif->media->map(fn (Media $media) => $media->url())->all(),
                    ])->all();

                return view('sitemap.urls', ['urls' => $urls])->render();
            });

        return $this->xml($xml);
    }

    /** Un paquet de pages image, avec leur visuel (balise image:image, pour Google Images). */
    public function images(Request $requete, int $paquet): Response
    {
        $marque = $this->marque($requete);

        abort_if($paquet < 1 || $paquet > 1000, 404);

        $xml = Cache::remember('sitemap:images:'.$marque->code.':'.$paquet, now()->addHours(self::CACHE_HEURES),
            function () use ($marque, $paquet) {
                $images = $this->imagesQuery($marque)
                    ->with('user:id,login,brand')
                    ->orderBy('media.id')
                    ->skip(($paquet - 1) * self::PAR_FICHIER)->take(self::PAR_FICHIER)
                    ->get(['media.id', 'media.user_id', 'media.gallery_id', 'media.filename', 'media.ai_title', 'media.analysed_at'])
                    ->map(fn (Media $media) => [
                        'loc' => $this->url($marque, parse_url($media->pageUrl(), PHP_URL_PATH)),
                        'image' => $media->url(),
                        'date' => $media->analysed_at?->toAtomString(),
                    ])->all();

                return view('sitemap.images', ['images' => $images])->render();
            });

        return $this->xml($xml);
    }

    /** Pages image indexables : meme regle que la page (Media::indexables). */
    private function imagesQuery(Marque $marque)
    {
        return Media::visiblesSurPortail($marque->code)->indexables();
    }

    /**
     * Books visibles, meme regle que le portail (BookRepository::baseQuery) :
     * diffuses, non suspendus, avec au moins un visuel publie hors portfolio
     * protege. Sans le visuel, le sitemap annoncait ~61 000 books dont
     * ~14 000 vides.
     */
    private function booksQuery(Marque $marque)
    {
        return User::query()
            ->where('brand', $marque->code)
            ->whereNull('blocked_at')
            ->whereHas('bookSetting', fn ($q) => $q->where('diffuse_web', true)->where('diffuse_ub', true))
            ->whereHas('media', fn ($q) => $q->published()->horsProteges()->whereNot('filename', ''))
            // Books en sommeil : en ligne, mais pas annonces aux moteurs (noindex).
            ->horsSommeil();
    }

    private function marque(Request $requete): Marque
    {
        return $requete->attributes->get('marque') ?? Marque::defaut();
    }

    private function url(Marque $marque, string $chemin): string
    {
        return rtrim($marque->canonique, '/')
            .($marque->multilingue() ? '/'.app()->getLocale() : '')
            .$chemin;
    }

    private function xml(mixed $contenu): Response
    {
        return response((string) $contenu, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
