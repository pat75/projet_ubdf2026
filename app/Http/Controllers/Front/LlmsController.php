<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Repository\BookRepository;
use App\Support\Marque;
use App\Support\Metier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * llms.txt (https://llmstxt.org) : presentation du portail, en Markdown,
 * a l'intention des assistants IA (ChatGPT, Claude, Perplexity…) —
 * referencement « GEO ». Ce qu'est le site, ses metiers avec leur adresse
 * et le nombre de portfolios, et les pages qui repondent aux questions
 * courantes. Propre a la marque servie, comme robots.txt et le sitemap.
 */
class LlmsController extends Controller
{
    private const CACHE_HEURES = 6;

    public function __construct(private readonly BookRepository $books) {}

    public function __invoke(Request $requete): Response
    {
        $marque = $requete->attributes->get('marque') ?? Marque::defaut();

        $texte = Cache::remember('llms.'.$marque->code.'.'.app()->getLocale(), now()->addHours(self::CACHE_HEURES),
            fn () => view('seo.llms', $this->donnees($marque))->render());

        return response($texte, 200, ['Content-Type' => 'text/markdown; charset=UTF-8']);
    }

    /** @return array<string, mixed> */
    private function donnees(Marque $marque): array
    {
        $racine = rtrim($marque->canonique, '/');
        $absolu = fn (string $url) => $racine.(parse_url($url, PHP_URL_PATH) ?: '/');
        $comptes = $this->books->countsByCategory($marque->code);

        return [
            'marque' => $marque,
            'racine' => $racine,
            'total' => $this->books->count(null, $marque->code),
            'metiers' => Metier::blocsAccueil()->map(fn (array $metier) => [
                'titre' => Metier::titreBloc($metier['slug']),
                'description' => Metier::sousTitre($metier['slug']),
                'url' => $absolu(lien_metier($metier['slug'])),
                'total' => $comptes[$metier['slug']] ?? 0,
                // Quelques books de la selection : les assistants IA citent
                // volontiers des noms quand on leur demande un createur.
                'books' => $this->books->portfolios('sel', $metier['slug'], 0, $marque->code, 5)
                    ->map(fn ($book) => [
                        'nom' => mb_convert_case($book->fullName(), MB_CASE_TITLE),
                        'url' => $book->bookUrl(),
                        'ville' => $book->city ? mb_convert_case($book->city, MB_CASE_TITLE) : null,
                        'specialites' => Tag::principauxDe($book->id, 3)->pluck('label')->implode(', '),
                    ])->all(),
            ])->filter(fn (array $metier) => $metier['total'] > 0)->values(),
            'recherche' => $absolu(lien('recherche')),
            // Questions frequentes des pages metier, formulees pour tous les
            // createurs, et pages thematiques (config/seo_contenus.php).
            'faq' => collect(config('seo_contenus.faq', []))->map(fn (array $entree) => [
                'question' => __($entree['question'], ['metier' => __('créatif'), 'metiers' => __('créatifs'), 'marque' => $marque->nom]),
                'reponse' => __($entree['reponse'], ['metier' => __('créatif'), 'metiers' => __('créatifs'), 'marque' => $marque->nom]),
            ])->all(),
            'thematiques' => collect(config('seo_contenus.landings', []))
                ->map(fn (array $page, string $chemin) => [
                    'titre' => __($page['titre']),
                    'description' => __($page['description']),
                    'url' => $absolu(lien('landing.'.$chemin)),
                ])->values()->all(),
            'inscription' => $absolu(lien('inscription.page')),
        ];
    }
}
