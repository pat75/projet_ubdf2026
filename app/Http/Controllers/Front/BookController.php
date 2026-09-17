<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BookSection;
use App\Models\Gallery;
use App\Models\User;
use App\Support\Marque;
use Illuminate\View\View;

/**
 * Book public, servi sur <login>.<book_domain>.
 *
 * Le legacy proposait onze themes graphiques (`mdl_2016_zoom`,
 * `mdl_2015_grid`, `mdl_2012_slide`…), voir `config/categories.php`. Ce
 * controleur rend un gabarit unique : reprendre chaque habillage pixel
 * pres n'entre pas dans ce lot. `book_settings.theme` reste importe et
 * disponible pour une reprise ulterieure par theme.
 *
 * La structure, elle, suit le legacy : les galeries portent les visuels
 * (le « portfolio » a proprement parler), les rubriques (`book_sections`)
 * portent les pages de texte (a propos, contact…). Ce sont deux arbres
 * independants, comme dans les tables d'origine.
 */
class BookController extends Controller
{
    /** Accueil du book : presentation et galeries de premier niveau. */
    public function accueil(string $login): View
    {
        return view('book.accueil', $this->navigation($this->trouver($login)));
    }

    /** Une galerie : ses sous-galeries et ses visuels publies. */
    public function galerie(string $login, string $slug): View
    {
        $book = $this->trouver($login);

        $galerie = Gallery::query()
            ->where('user_id', $book->id)
            ->where('slug', $slug)
            ->published()
            ->with([
                'children' => fn ($query) => $query->published(),
                'media' => fn ($query) => $query->published()->orderBy('position'),
            ])
            ->firstOrFail();

        return view('book.galerie', $this->navigation($book) + ['galerie' => $galerie]);
    }

    /**
     * Une rubrique : ses articles publies.
     *
     * Une rubrique privee (`is_private`) n'est visible qu'au proprietaire
     * connecte — c'est le mode « brouillon » du legacy, repris tel quel.
     */
    public function rubrique(string $login, string $slug): View
    {
        $book = $this->trouver($login);

        $rubrique = BookSection::query()
            ->where('user_id', $book->id)
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with([
                'children' => fn ($query) => $query->where('is_published', true),
                'articles' => fn ($query) => $query->published()->orderBy('position'),
            ])
            ->firstOrFail();

        if ($rubrique->is_private && auth()->id() !== $book->id) {
            abort(403);
        }

        return view('book.rubrique', $this->navigation($book) + ['rubrique' => $rubrique]);
    }

    /**
     * Retrouve le compte du sous-domaine, ou 404.
     *
     * `SoftDeletes` exclut deja les comptes supprimes des requetes
     * standard ; un compte inexistant ou efface rend donc naturellement
     * 404, sans distinction supplementaire a coder ici.
     */
    private function trouver(string $login): User
    {
        return User::with('bookSetting', 'category')
            ->where('login', $login)
            ->firstOrFail();
    }

    /** Donnees communes a toutes les pages du book : le compte et son menu. */
    private function navigation(User $book): array
    {
        return [
            'book' => $book,
            'marque' => Marque::depuisCode($book->brand),
            'navGaleries' => $this->galeriesNav($book),
            'navRubriques' => $book->sections()
                ->where('is_published', true)->whereNull('parent_id')
                ->orderBy('position')->get(),
        ];
    }

    private function galeriesNav(User $book)
    {
        return $book->galleries()->published()->whereNull('parent_id')->orderBy('position')->get();
    }
}
