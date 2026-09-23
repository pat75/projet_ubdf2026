<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\User;
use App\Support\Marque;
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

    public function index(Request $requete): Response
    {
        $marque = $this->marque($requete);
        $paquets = (int) ceil(max(1, $this->booksQuery($marque)->count()) / self::PAR_FICHIER);

        $fichiers = [$this->url($marque, '/sitemap-pages.xml')];

        for ($i = 1; $i <= $paquets; $i++) {
            $fichiers[] = $this->url($marque, '/sitemap-books-'.$i.'.xml');
        }

        return $this->xml(view('sitemap.index', ['fichiers' => $fichiers]));
    }

    /** Pages fixes : accueil, metiers, landings SEO, documentation, actus. */
    public function pages(Request $requete): Response
    {
        $marque = $this->marque($requete);

        $xml = Cache::remember('sitemap:pages:'.$marque->code.':'.app()->getLocale(), now()->addHours(self::CACHE_HEURES),
            function () use ($marque) {
                $urls = [['loc' => $this->url($marque, '/accueil'), 'priorite' => '1.00']];

                foreach (Category::where('is_active', true)->orderBy('position')->pluck('slug') as $slug) {
                    $urls[] = ['loc' => $this->url($marque, '/'.$slug), 'priorite' => '0.80'];
                }

                foreach (array_keys(config('seo_routes.landings')) as $chemin) {
                    $urls[] = ['loc' => $this->url($marque, '/'.$chemin), 'priorite' => '0.60'];
                }

                foreach (CmsPage::query()->pluck('slug') as $slug) {
                    $urls[] = ['loc' => $this->url($marque, '/doc/'.$slug), 'priorite' => '0.50'];
                }

                foreach (CmsPost::query()->latest('published_at')->limit(200)->pluck('slug') as $slug) {
                    $urls[] = ['loc' => $this->url($marque, '/actus/'.$slug), 'priorite' => '0.40'];
                }

                return view('sitemap.urls', ['urls' => $urls])->render();
            });

        return $this->xml($xml);
    }

    /** Un paquet de books diffuses. */
    public function books(Request $requete, int $paquet): Response
    {
        $marque = $this->marque($requete);

        abort_if($paquet < 1 || $paquet > 1000, 404);

        $xml = Cache::remember('sitemap:books:'.$marque->code.':'.$paquet, now()->addHours(self::CACHE_HEURES),
            function () use ($marque, $paquet) {
                $urls = $this->booksQuery($marque)
                    ->orderBy('id')
                    ->skip(($paquet - 1) * self::PAR_FICHIER)->take(self::PAR_FICHIER)
                    ->get(['id', 'login', 'firstname', 'lastname', 'category_id', 'updated_at'])
                    ->map(fn (User $creatif) => [
                        'loc' => $creatif->bookUrl(),
                        'priorite' => '0.70',
                        'date' => $creatif->updated_at?->toAtomString(),
                    ])->all();

                return view('sitemap.urls', ['urls' => $urls])->render();
            });

        return $this->xml($xml);
    }

    /** Books publiés : en ligne et acceptant le portail. */
    private function booksQuery(Marque $marque)
    {
        return User::query()
            ->where('brand', $marque->code)
            ->whereHas('bookSetting', fn ($q) => $q->where('diffuse_web', true)->where('diffuse_ub', true));
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
