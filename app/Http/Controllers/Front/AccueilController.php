<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repository\BookRepository;
use App\Support\Metier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $counts = $this->books->countsByCategory($brand);

        $blocs = Metier::blocsAccueil()->map(fn (array $metier) => [
            'slug' => $metier['slug'],
            'books' => $this->books->portfolios('sel', $metier['slug'], 0, $brand, self::PAR_BLOC),
            'total' => $counts[$metier['slug']] ?? 0,
        ])->reject(fn (array $bloc) => $bloc['books']->isEmpty());

        return view('front.accueil', [
            'blocs' => $blocs,
            'ubdf' => [
                'per_page' => BookRepository::PER_PAGE,
                'book_domain' => config('ubdf.book_domain'),
            ],
        ]);
    }

    /**
     * Page d'une categorie : liste continue, alimentee par le defilement
     * infini a partir du deuxieme ecran.
     */
    public function categorie(Request $request, string $categorie): View
    {
        $brand = $request->attributes->get('brand', 'ub');

        return view('front.categorie', [
            'categorie' => $categorie,
            'books' => $this->books->portfolios('sel', $categorie, 0, $brand),
            'total' => $this->books->count($categorie, $brand),
            'ubdf' => [
                'per_page' => BookRepository::PER_PAGE,
                'total' => $this->books->count($categorie, $brand),
                'category' => $categorie,
                'selection' => 'sel',
                'book_domain' => config('ubdf.book_domain'),
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
     * Defilement infini du portail.
     *
     * Contrat repris du legacy : GET /accueil__<page>__<sel|ult|lub>__<type>
     * renvoie un tableau JSON dont les cles sont celles attendues par
     * js2019/js_core_cards.js. Elles ne doivent pas etre renommees tant que
     * ce JavaScript n'est pas reecrit (phase 9).
     */
    public function ajax(Request $request, int $page, string $selection, string $type): JsonResponse
    {
        $brand = $request->attributes->get('brand', 'ub');

        $books = $this->books->portfolios($selection, $type, $page, $brand);

        return response()->json($books->map(fn ($book) => [
            'us_id' => (string) $book->id,
            'us_key' => $book->publicKey(),
            'us_formule' => $book->plan > 0,
            'us_statut' => $book->status,
            'us_prenom' => $book->firstname,
            'us_nom' => $book->lastname,
            'us_dir' => $book->login,
            'us_type' => $book->category?->name,
            'us_type_titre' => $book->status,
            'us_date' => $book->created_at?->toDateString(),
            'us_ville' => $book->city,
            'us_pays' => $book->country,
            'us_lat' => (string) ($book->latitude ?? ''),
            'us_lng' => (string) ($book->longitude ?? ''),
            'us_twitter_url' => $book->twitter_url,
            'us_facebook_url' => $book->facebook_url,
            'us_pf_img_vignette' => $book->thumbnailUrl(),
            'us_pf_diff_dispo' => $book->is_available ? 'true' : 'false',
            'us_path' => '/books/'.$book->login,
            'stats_st_cles' => $book->publicKey(),
            'img' => $book->media->map(fn ($media) => [
                'img_id' => (string) $media->id,
                'img_titre' => $media->title,
                'img_fichier' => $media->url(),
            ])->values(),
            'slider' => $book->media->map(fn ($media) => [
                'fichier' => $media->url(),
                'title' => $media->title,
            ])->values(),
        ])->values());
    }
}
