<?php

namespace App\Livewire\Espace;

use App\Livewire\Concerns\EnregistreChamps;
use App\Models\BookArticle;
use App\Models\BookSection;
use App\Services\Espace\NettoyeurHtml;
use App\Services\Espace\RenduBlocsPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Rubriques de pages du book (bio, actualites, pages d'accueil) et leurs
 * pages : pag_add, rub_* de categorie 1 et 3 du legacy. Chaque page
 * s'ouvre en accordeon sous son titre (meme fonctionnement que les
 * portfolios de App\Livewire\Espace\Galeries), pas sur un ecran a part.
 */
class Pages extends Component
{
    use EnregistreChamps;

    /** Page actuellement depliee, ou null. */
    public ?int $edition = null;

    /** Contenu Redactor de la page depliee (config('pages.editeur_texte') === 'redactor'). */
    public string $corps = '';

    /** Blocs Editor.js de la page depliee (config('pages.editeur_texte') === 'redactor_bloc'). */
    public array $blocs = [];

    /**
     * Nouvelle rubrique sans nom (« Nouvelle rubrique » n'est qu'une
     * invite), a nommer sur place ; masquee jusqu'a ce qu'elle soit nommee,
     * pour ne pas laisser d'entree vide dans le menu du book.
     */
    public function creerRubrique(): void
    {
        Auth::user()->sections()->create([
            'kind' => BookSection::PAGES,
            'title' => '',
            'slug' => 'rubrique',
            'is_published' => false,
            // En tete de liste : avant la premiere rubrique existante.
            'position' => (int) Auth::user()->sections()->min('position') - 1,
        ]);
    }

    public function basculerRubrique(int $id): void
    {
        $rubrique = $this->rubrique($id);
        $rubrique->update(['is_published' => ! $rubrique->is_published]);
    }

    public function supprimerRubrique(int $id): void
    {
        $rubrique = $this->rubrique($id);

        // Une rubrique ne se supprime que vide : ses pages d'abord, une a une.
        if ($rubrique->articles()->exists()) {
            return;
        }

        $rubrique->delete();
    }

    /**
     * Nouvelle page sans titre (« Nouvelle page » n'est qu'une invite),
     * ouverte aussitot ; brouillon jusqu'au premier enregistrement.
     */
    public function creerPage(int $rubriqueId): void
    {
        $rubrique = $this->rubrique($rubriqueId);
        $page = $rubrique->articles()->create([
            'user_id' => Auth::id(),
            'title' => '',
            'slug' => 'page',
            'status' => 'draft',
            'position' => (int) $rubrique->articles()->max('position') + 1,
        ]);

        if ($rubrique->page_order) {
            $rubrique->update(['page_order' => [...$rubrique->page_order, (string) $page->id]]);
        }

        $this->ouvrirPage($page->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Edition d'une page, en accordeon (voir Galeries::updatedFichiers et
    | consorts pour le meme principe applique aux visuels)
    |--------------------------------------------------------------------------
    */

    public function ouvrirPage(int $id): void
    {
        if ($this->edition === $id) {
            $this->fermerPage();

            return;
        }

        $page = $this->page($id);
        $this->edition = $id;
        // Anciennes URL /users_2/... : reecrites pour l'editeur, et donc
        // enregistrees sous leur forme actuelle au prochain enregistrement.
        $this->corps = urls_medias_book((string) $page->body);
        $this->blocs = $page->body_blocks ?? [];
        $this->resetErrorBag(['corps', 'blocs']);
    }

    public function enregistrerPage(NettoyeurHtml $nettoyeur): void
    {
        $this->validate(['corps' => 'nullable|string|max:200000']);

        $this->page($this->edition)->update(['body' => $nettoyeur->nettoyer($this->corps), 'status' => 'published']);
    }

    /** Enregistrement pour config('pages.editeur_texte') === 'redactor_bloc' : voir resources/js/espace-blocs.js. */
    public function enregistrerBlocs(RenduBlocsPage $rendu, NettoyeurHtml $nettoyeur): void
    {
        $this->validate(['blocs' => 'array']);

        $this->page($this->edition)->update([
            'body_blocks' => $this->blocs,
            'body' => $nettoyeur->nettoyer($rendu->versHtml($this->blocs)),
            'status' => 'published',
        ]);
    }

    public function supprimerPage(int $id): void
    {
        $this->page($id)->delete();

        if ($this->edition === $id) {
            $this->fermerPage();
        }
    }

    private function fermerPage(): void
    {
        $this->edition = null;
        $this->corps = '';
        $this->blocs = [];
    }

    private function page(int $id): BookArticle
    {
        $page = BookArticle::findOrFail($id);
        $this->authorize('update', $page->section);

        return $page;
    }

    /** @param  list<int|string>  $ids */
    public function ordonnerPages(int $rubriqueId, array $ids): void
    {
        $rubrique = $this->rubrique($rubriqueId);
        $pages = $rubrique->articles()->whereIn('id', $ids)->get()->keyBy('id');

        $rubrique->update(['page_order' => collect($ids)
            ->map(fn ($id) => $pages->get((int) $id))->filter()
            ->map(fn (BookArticle $p) => (string) ($p->legacy_id ?? $p->id))
            ->values()->all()]);
    }

    public function render(): View
    {
        $rubriques = Auth::user()->sections()->with('articles')->orderBy('position')->orderBy('id')->get();

        foreach ($rubriques as $rubrique) {
            $ordre = array_flip($rubrique->page_order ?? []);
            $rubrique->setRelation('articles', $rubrique->articles->sortBy(
                fn (BookArticle $p) => [$ordre[(string) ($p->legacy_id ?? $p->id)] ?? PHP_INT_MAX, $p->legacy_id ?? $p->id]
            )->values());
        }

        return view('livewire.espace.pages', [
            'rubriques' => $rubriques,
            'editeurTexte' => $this->editeurTexte(),
            'devEditeur' => $this->devEditeur(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Moteur d'edition : config('pages.editeur_texte'), que la bascule de
    | developpement (session) peut remplacer, en local seulement.
    |--------------------------------------------------------------------------
    */

    private const EDITEURS = [
        'redactor' => 'Redactor 3.5.2 classique',
        'redactor_bloc' => 'Redactor bloc (Editor.js)',
    ];

    private function devEditeur(): bool
    {
        return app()->environment(['local', 'development']);
    }

    private function editeurTexte(): string
    {
        $choix = $this->devEditeur() ? session('dev.editeur_pages') : null;

        return isset(self::EDITEURS[$choix]) ? $choix : config('pages.editeur_texte');
    }

    public function libelleEditeur(): string
    {
        return self::EDITEURS[$this->editeurTexte()] ?? $this->editeurTexte();
    }

    /** Bascule de developpement : passe a l'autre moteur et recharge la page. */
    public function basculerEditeur(): void
    {
        abort_unless($this->devEditeur(), 403);

        session(['dev.editeur_pages' => $this->editeurTexte() === 'redactor' ? 'redactor_bloc' : 'redactor']);
        $this->redirectRoute('espace.pages');
    }

    protected function champsAutoEnregistres(): array
    {
        $rubriques = Auth::user()->sections()->pluck('id');
        $regles = [];

        foreach ($rubriques as $id) {
            $regles['rubrique-'.$id] = ['required', 'string', 'max:100'];
        }

        foreach (BookArticle::whereIn('book_section_id', $rubriques)->pluck('id') as $id) {
            $regles['page-'.$id] = ['required', 'string', 'max:255'];
        }

        return $regles;
    }

    protected function persisterChamp(string $nom, mixed $valeur): void
    {
        [$champ, $id] = explode('-', $nom, 2);
        $valeur = trim((string) $valeur);

        match ($champ) {
            'rubrique' => $this->nommerRubrique($this->rubrique((int) $id), $valeur),
            'page' => $this->page((int) $id)->update(['title' => $valeur, 'slug' => Str::slug($valeur) ?: 'page']),
        };
    }

    private function nommerRubrique(BookSection $rubrique, string $titre): void
    {
        $premiereFois = $rubrique->title === '';

        $rubrique->update([
            'title' => $titre,
            'slug' => Str::slug($titre) ?: 'rubrique',
            'is_published' => $premiereFois ? true : $rubrique->is_published,
        ]);
    }

    private function rubrique(int $id): BookSection
    {
        $rubrique = BookSection::findOrFail($id);
        $this->authorize('update', $rubrique);

        return $rubrique;
    }
}
