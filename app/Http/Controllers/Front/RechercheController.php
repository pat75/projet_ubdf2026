<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\SearchQuery;
use App\Repository\BookRepository;
use App\Support\CarteLegacy;
use App\Support\Recherche;
use App\Support\SuggestionsMotsCles;
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
        $recherche = Recherche::depuisRequete($request);
        $donnees = $this->donnees($recherche) + ['ajax' => true];

        if (! $request->ajax()) {
            // Page d'accueil du moteur : les mots-cles en vogue sous le bloc.
            if ($recherche->q === '') {
                $donnees['populaires'] = $this->populaires($recherche->brand);
                $donnees['portfoliosDuMoment'] = $this->portfoliosDuMoment($donnees['populaires'], $recherche->brand);
            }

            return view('front.recherche', $donnees);
        }

        return response()->json([
            'html' => view('front.partials.resultats-recherche', $donnees)->render(),
            'total' => $donnees['total'],
            'ubdf' => $donnees['ubdf'],
        ]);
    }

    /**
     * Mots-cles les plus recherches sur 90 jours. Tant que le journal est
     * vide (mise en service), les domaines metier en tiennent lieu.
     *
     * Chaque mot-cle porte son domaine (« illustration,jeunesse ») : il
     * donne la couleur `coul_<domaine>` des etiquettes de l'accueil.
     *
     * @return list<array{q: string, mot: string, domaine: ?string}>
     */
    private function populaires(string $brand): array
    {
        $motcles = json_decode(file_get_contents(resource_path('js/portail/motcles.json')), true);
        $domaines = array_keys($motcles[app()->getLocale() === 'en' ? 'en' : 'fr'] ?? []);

        // Jusqu'a 48 mots-cles du journal, completes par 20 au plus tires au
        // hasard parmi les mots-cles du moteur (motcles.json).
        $populaires = SearchQuery::populaires($brand, limite: 48)->all();
        $nombre = min(48, count($populaires)) + 20;

        if (count($populaires) < $nombre) {
            $catalogue = [];
            foreach ($motcles[app()->getLocale() === 'en' ? 'en' : 'fr'] ?? [] as $domaine => $mots) {
                $catalogue[] = $domaine;
                foreach ($mots as $mot) {
                    $catalogue[] = mb_strtolower($domaine.','.$mot);
                }
            }
            $complement = collect(array_diff(array_unique($catalogue), $populaires))
                ->shuffle()->take($nombre - count($populaires));
            $populaires = [...$populaires, ...$complement];
        }

        return array_map(function (string $q) use ($domaines) {
            [$tete, $mot] = str_contains($q, ',') ? array_map('trim', explode(',', $q, 2)) : [$q, $q];

            return [
                'q' => $q,
                'mot' => $mot,
                'domaine' => in_array($tete, $domaines, true) ? $tete : null,
            ];
        }, $populaires);
    }

    /**
     * Cinq portfolios tires des recherches du moment : le meilleur resultat
     * de chaque mot-cle, dans l'ordre de la liste, sans doublon. Au plus 12
     * mots-cles interroges, pour borner le cout de la page.
     *
     * @param  list<array{q: string}>  $populaires
     * @return \Illuminate\Support\Collection<int, \App\Models\User>
     */
    private function portfoliosDuMoment(array $populaires, string $brand, int $nombre = 5)
    {
        $books = collect();

        foreach (array_slice($populaires, 0, 12) as $populaire) {
            $recherche = new Recherche(q: $populaire['q'], mode: 'mcles', brand: $brand);
            if (! $recherche->exploitable()) {
                continue;
            }

            $book = $this->books->rechercher($recherche)->first(fn ($b) => ! $books->has($b->id));
            if ($book) {
                $books->put($book->id, $book);
            }
            if ($books->count() === $nombre) {
                break;
            }
        }

        return $books->values();
    }

    /** @return array<string, mixed> */
    private function donnees(Recherche $recherche): array
    {
        SearchQuery::journaliser($recherche);

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

    /** Auto-completion du champ de recherche (recherche.js), des 3 caracteres. */
    public function suggestions(Request $request): JsonResponse
    {
        return response()->json(SuggestionsMotsCles::pour((string) $request->query('q', '')));
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
