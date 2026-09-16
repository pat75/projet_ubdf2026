<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $book = User::with(['category', 'bookSetting', 'media' => fn ($query) => $query->published()])
            ->where('login', $login)
            ->where('is_published', true)
            ->firstOrFail();

        if (! str_ends_with($book->portfolioUrl(), $slug)) {
            return redirect($book->portfolioUrl(), 301);
        }

        return view('front.portfolio', ['book' => $book]);
    }
}
