<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repository\BookRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccueilController extends Controller
{
    public function __construct(private readonly BookRepository $books) {}

    /** Page d'accueil : premier ecran de books rendu cote serveur. */
    public function index(Request $request, ?string $category = null): View
    {
        $brand = $request->attributes->get('brand', 'ub');

        return view('front.accueil', [
            'books' => $this->books->portfolios('sel', $category, 0, $brand),
            'category' => $category,
            'ubdf' => [
                'per_page' => BookRepository::PER_PAGE,
                'total' => $this->books->count($category, $brand),
                'category' => $category ?? 'all',
                'book_domain' => config('ubdf.book_domain'),
            ],
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
