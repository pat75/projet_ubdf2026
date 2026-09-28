<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repository\BookRepository;
use App\Support\CarteLegacy;
use App\Support\Recherche;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RechercheController extends Controller
{
    public function __construct(private readonly BookRepository $books) {}

    /**
     * Page de resultats, rendue cote serveur.
     *
     * Le front 2018 n'avait pas d'equivalent : la recherche n'existait que
     * dans le navigateur, sur une page d'accueil dont il remplacait le
     * contenu. Une URL propre (`/recherche?q=…`) est partageable, indexable
     * et fonctionne sans JavaScript.
     */
    public function page(Request $request): View
    {
        return view('front.recherche', $this->donnees(Recherche::depuisRequete($request)));
    }

    /**
     * Page `/search`, ouverte par la loupe du menu : meme bloc que
     * l'accueil, mais le formulaire y est soumis en ajax et les resultats
     * s'affichent dessous, sans recharger la page. Appelee en ajax, elle
     * ne rend que ce fragment.
     */
    public function search(Request $request): View|JsonResponse
    {
        $donnees = $this->donnees(Recherche::depuisRequete($request)) + ['ajax' => true];

        if (! $request->ajax()) {
            return view('front.recherche', $donnees);
        }

        return response()->json([
            'html' => view('front.partials.resultats-recherche', $donnees)->render(),
            'total' => $donnees['total'],
            'ubdf' => $donnees['ubdf'],
        ]);
    }

    /** @return array<string, mixed> */
    private function donnees(Recherche $recherche): array
    {
        $total = $recherche->exploitable()
            ? $this->books->compterRecherche($recherche)
            : 0;

        return [
            'recherche' => $recherche,
            'books' => $recherche->exploitable() ? $this->books->rechercher($recherche) : collect(),
            'total' => $total,
            'ubdf' => [
                'per_page' => BookRepository::PER_PAGE,
                'total' => $total,
                'cartes_url' => '/recherche/cartes',
                'cartes_params' => [
                    'q' => $recherche->q,
                    'recherche' => $recherche->mode,
                    'anu_type' => $recherche->categorie,
                    'flt_sel' => $recherche->selection ? 'true' : 'false',
                    'flt_pro' => $recherche->abonnes ? 'true' : 'false',
                ],
                'book_domain' => config('ubdf.book_domain'),
            ],
        ];
    }

    /** Cartes suivantes du defilement, rendues en HTML comme le premier ecran. */
    public function cartes(Request $request, int $page): JsonResponse
    {
        $recherche = Recherche::depuisRequete($request)->page($page);

        $books = $recherche->exploitable()
            ? $this->books->rechercher($recherche)
            : collect();

        return response()->json([
            'html' => view('front.partials.cartes', ['books' => $books])->render(),
            'count' => $books->count(),
            'fin' => $books->count() < BookRepository::PER_PAGE,
        ]);
    }

    /**
     * Contrat du front 2018 : GET /rechercher_submit, parametres a plat,
     * reponse en tableau JSON. Conserve tant que `js_core_pages.js` n'est
     * pas reecrit — c'est lui qui construit cette requete.
     *
     * Une recherche trop courte rend un tableau vide, et non une erreur :
     * le JavaScript affiche alors son message « aucun resultat », alors
     * qu'un code 4xx le laisserait sur son indicateur de chargement.
     */
    public function legacy(Request $request): JsonResponse
    {
        $recherche = Recherche::depuisRequete($request);

        if (! $recherche->exploitable()) {
            return response()->json([]);
        }

        return response()->json(
            $this->books->rechercher($recherche)->map(CarteLegacy::depuis(...))->values()
        );
    }
}
