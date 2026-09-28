<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
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
                'url' => $absolu(lien('categorie', ['categorie' => $metier['slug']])),
                'total' => $comptes[$metier['slug']] ?? 0,
            ])->filter(fn (array $metier) => $metier['total'] > 0)->values(),
            'recherche' => $absolu(lien('recherche')),
            'inscription' => $absolu(lien('inscription.page')),
        ];
    }
}
