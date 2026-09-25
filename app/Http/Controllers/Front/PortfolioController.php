<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Marque;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    /**
     * Fiche d'un book sur le portail : /portfolio/<login>/<slug>.
     *
     * Le slug ne sert qu'au referencement. S'il ne correspond pas a celui
     * attendu, on redirige vers l'URL canonique plutot que d'accepter des
     * variantes qui dupliqueraient le contenu aux yeux des moteurs.
     */
    public function show(string $login, string $slug): View|RedirectResponse
    {
        $marque = request()->attributes->get('marque') ?? Marque::defaut();

        /*
         | La visibilite se lit sur les deux drapeaux de diffusion
         | (`diffuse_web`, `diffuse_ub`), pas sur `in_home_selection` : ce
         | dernier ne designe que la selection editoriale mise en avant.
         | Meme correction que dans BookRepository::baseQuery() — voir sa
         | note pour le detail (8 comptes Dustfolio invisibles a tort).
         */
        $book = User::with(['category', 'bookSetting', 'media' => fn ($query) => $query->published()->horsProteges()])
            ->where('login', $login)
            ->where('brand', $marque->code)
            ->whereHas('bookSetting', fn ($query) => $query
                ->where('diffuse_web', true)
                ->where('diffuse_ub', true))
            ->firstOrFail();

        if (! str_ends_with($book->portfolioUrl(), $slug)) {
            return redirect($book->portfolioUrl(), 301);
        }

        return view('front.portfolio', ['book' => $book]);
    }
}
