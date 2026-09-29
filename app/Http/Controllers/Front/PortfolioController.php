<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Marque;
use Illuminate\Http\RedirectResponse;

class PortfolioController extends Controller
{
    /**
     * Ancienne fiche d'un book sur le portail : /portfolio/<login>/<slug>.
     *
     * La fiche n'est plus affichee : l'URL historique (liens entrants,
     * referencement) redirige en 301 vers le book lui-meme.
     */
    public function show(string $login, string $slug): RedirectResponse
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

        return redirect()->away($book->bookUrl(), 301);
    }
}
