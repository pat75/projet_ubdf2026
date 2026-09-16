<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;
use App\Repository\BookRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Compteurs globaux du portail (/cache_js/data_stats.json).
 *
 * Le legacy servait un fichier JSON regenere periodiquement par une tache.
 * Ici la reponse est calculee depuis la base et mise en cache : pas de
 * fichier a regenerer, et surtout pas de chiffres de production figes dans
 * le depot. Le format est celui attendu par js_core_pages.js, qui alimente
 * les compteurs du menu et le bloc d'exemples de books.
 */
class StatsController extends Controller
{
    private const DUREE_CACHE = 3600;

    public function __construct(private readonly BookRepository $books) {}

    public function __invoke(Request $request): JsonResponse
    {
        $brand = $request->attributes->get('brand', 'ub');

        $donnees = Cache::remember("stats_portail_{$brand}", self::DUREE_CACHE, function () use ($brand) {
            $parCategorie = $this->books->countsByCategory($brand);

            return [
                'menu_stats' => [
                    'nb_book' => $this->format(User::where('brand', $brand)->where('in_home_selection', true)->count()),
                    'nb_selection' => $this->format(User::where('brand', $brand)->where('is_selected', true)->count()),
                    'nb_visuel' => $this->format(Media::published()->count()),
                    'nb_galerie' => $this->format(Gallery::published()->count()),
                    'nb_book_illustrateur' => $this->format($parCategorie['illustrateur'] ?? 0),
                    'nb_book_illustrateur_jeunesse' => $this->format($parCategorie['illustrateur-jeunesse'] ?? 0),
                    'nb_book_graphiste' => $this->format($parCategorie['graphiste'] ?? 0),
                ],
                'menu_book_exemple' => $this->exemples($brand),
            ];
        });

        return response()->json($donnees);
    }

    /** Books mis en avant dans le menu. */
    private function exemples(string $brand): array
    {
        return User::with('category')
            ->where('brand', $brand)
            ->where('in_home_selection', true)
            ->where('is_selected', true)
            ->inRandomOrder()
            ->limit(13)
            ->get()
            ->map(fn (User $book) => [
                'nom' => $book->fullName(),
                'url' => $book->bookUrl().'/',
                'type' => $book->category?->name ?? '',
            ])
            ->all();
    }

    /** Le front affiche les nombres tels quels : « 13 164 ». */
    private function format(int $nombre): string
    {
        return number_format($nombre, 0, ',', ' ');
    }
}
