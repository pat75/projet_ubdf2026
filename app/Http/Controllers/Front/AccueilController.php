<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\AccueilBloc;
use App\Repository\BookRepository;
use App\Support\CarteLegacy;
use App\Support\Metier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AccueilController extends Controller
{
    /** Books affiches dans chaque bloc metier de l'accueil. */
    private const PAR_BLOC = 9;

    public function __construct(private readonly BookRepository $books) {}

    /**
     * Page d'accueil : un bloc par metier, comme le front 2018.
     *
     * Chaque bloc porte son titre, une selection de books, le nombre total
     * de creatifs de la categorie et le lien vers la page du metier.
     */
    public function index(Request $request): View
    {
        $brand = $request->attributes->get('brand', 'ub');

        // Les blocs sont les memes pour tous les visiteurs : requetes et
        // rendu (90 cartes, ~300 ms sur la prod) gardes 10 minutes. Vides
        // des qu'une selection change (User, hook `saved`).
        $cache = Cache::remember(self::cleCache($brand, app()->getLocale()), now()->addMinutes(10), function () use ($brand) {
            $counts = $this->books->countsByCategory($brand);

            $blocs = Metier::blocsAccueil()->map(fn (array $metier) => [
                'slug' => $metier['slug'],
                'books' => $this->books->portfolios('sel', $metier['slug'], 0, $brand, self::PAR_BLOC),
                'total' => $counts[$metier['slug']] ?? 0,
            ])->reject(fn (array $bloc) => $bloc['books']->isEmpty());

            return [
                'slugs' => $blocs->pluck('slug')->values()->all(),
                'html' => view('front.partials.blocs-accueil', ['blocs' => $blocs])->render(),
            ];
        });

        return view('front.accueil', [
            // Le JSON-LD de la page ne lit que le slug de chaque bloc.
            'blocs' => collect($cache['slugs'])->map(fn (string $slug) => ['slug' => $slug]),
            'blocsHtml' => $cache['html'],
            // Blocs d'accroche affiches ou masques depuis le back-office
            // (App\Filament\Pages\AccueilPage), lus dans accueil-hero.
            'accueilBlocs' => AccueilBloc::etats(),
            'ubdf' => [
                'per_page' => BookRepository::PER_PAGE,
                'book_domain' => (\App\Support\Marque::depuisCode($request->attributes->get('brand', 'ub')))->domaineBooks,
            ],
        ]);
    }

    private static function cleCache(string $brand, string $langue): string
    {
        return "accueil_blocs_{$brand}_{$langue}";
    }

    /** Vide les blocs en cache de toutes les marques et langues. */
    public static function viderCache(): void
    {
        foreach (['ub', 'df'] as $brand) {
            foreach (['fr', 'en'] as $langue) {
                Cache::forget(self::cleCache($brand, $langue));
            }
        }

        BookRepository::viderCache();
    }

    /**
     * Page d'une categorie : liste continue, alimentee par le defilement
     * infini a partir du deuxieme ecran.
     */
    public function categorie(Request $request, string $categorie): View
    {
        // Segment d'URL (« illustrator » en anglais) -> slug interne.
        $categorie = Metier::depuisSlugUrl($categorie);

        $brand = $request->attributes->get('brand', 'ub');

        return view('front.categorie', [
            'categorie' => $categorie,
            // Page d'accroche SEO (config/seo_contenus.php), sinon null.
            'landing' => $request->route('landing') ? config('seo_contenus.landings.'.$request->route('landing')) : null,
            'books' => $this->books->portfolios('sel', $categorie, 0, $brand),
            'total' => $this->books->count($categorie, $brand),
            'ubdf' => [
                'per_page' => BookRepository::PER_PAGE,
                'total' => $this->books->count($categorie, $brand),
                'cartes_url' => '/cartes/'.$categorie,
                'cartes_params' => ['selection' => 'sel'],
                'book_domain' => (\App\Support\Marque::depuisCode($request->attributes->get('brand', 'ub')))->domaineBooks,
            ],
        ]);
    }

    /**
     * Cartes suivantes du defilement infini, rendues en HTML.
     *
     * Le legacy renvoyait du JSON que le navigateur assemblait avec un
     * template Handlebars : deux rendus a maintenir pour une meme carte, qui
     * finissaient par diverger. Ici le serveur rend le meme composant Blade
     * que le premier ecran, ce qui garantit qu'une carte chargee au
     * defilement est identique a une carte rendue au chargement.
     *
     * La classe « newitem_hide » masque chaque carte a l'arrivee ; le script
     * la retire une par une, ce qui produit le fondu en cascade du legacy
     * (regle CSS existante : opacite 0, translation de -30px, 0,3 s).
     */
    public function cartes(Request $request, string $categorie, int $page): JsonResponse
    {
        $brand = $request->attributes->get('brand', 'ub');
        $selection = $request->query('selection', 'sel');

        $books = $this->books->portfolios($selection, $categorie, $page, $brand);

        return response()->json([
            'html' => view('front.partials.cartes', ['books' => $books])->render(),
            'count' => $books->count(),
            'fin' => $books->count() < BookRepository::PER_PAGE,
        ]);
    }

    /**
     * Carte seule d'un book, pour l'ancre « #login » : sa carte peut ne pas
     * figurer dans le premier ecran de la page (defilement infini), la
     * visionneuse s'ouvre alors a partir de celle-ci.
     */
    public function carte(Request $request, string $login): JsonResponse
    {
        $book = $this->books->parLogin($login, $request->attributes->get('brand', 'ub'));

        abort_if($book === null, 404);

        return response()->json([
            'html' => view('front.partials.cartes', ['books' => collect([$book])])->render(),
        ]);
    }

    /**
     * Defilement infini du portail.
     *
     * Contrat repris du legacy : GET /accueil__<page>__<sel|ult|lub>__<type>
     * renvoie un tableau JSON dont les cles sont celles attendues par
     * js2019/js_core_cards.js. Elles ne doivent pas etre renommees tant que
     * ce JavaScript n'est pas reecrit (phase 9).
     */
    /**
     * Meme reponse, pour la forme `/accueil__sel__all__2` du legacy : les
     * parametres scalaires arrivent dans l'ordre de l'URI, d'ou cette
     * seconde porte plutot qu'un reordonnancement hasardeux.
     */
    public function ajaxInverse(Request $request, string $selection, string $type, int $page): JsonResponse
    {
        return $this->ajax($request, $page, $selection, $type);
    }

    public function ajax(Request $request, int $page, string $selection, string $type): JsonResponse
    {
        $brand = $request->attributes->get('brand', 'ub');

        $books = $this->books->portfolios($selection, $type, $page, $brand);

        return response()->json($books->map(CarteLegacy::depuis(...))->values());
    }
}
