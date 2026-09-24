<?php

namespace App\Livewire\Espace;

use App\Livewire\Concerns\EnregistreChamps;
use App\Models\BookArticle;
use App\Models\BookSection;
use App\Services\Espace\NettoyeurHtml;
use App\Services\Espace\RenduBlocsPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
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

    #[Validate('required|string|max:100')]
    public string $nom = '';

    /** Titre de la nouvelle page, par rubrique. */
    public array $nouvellePage = [];

    /** Page actuellement depliee, ou null. */
    public ?int $edition = null;

    /** Contenu Redactor de la page depliee (config('pages.editeur_texte') === 'redactor'). */
    public string $corps = '';

    /** Blocs Editor.js de la page depliee (config('pages.editeur_texte') === 'redactor_bloc'). */
    public array $blocs = [];

    public function creerRubrique(): void
    {
        $this->validate();

        Auth::user()->sections()->create([
            'kind' => BookSection::PAGES,
            'title' => $this->nom,
            'slug' => Str::slug($this->nom) ?: 'rubrique',
            'is_published' => true,
            'position' => (int) Auth::user()->sections()->max('position') + 1,
        ]);

        $this->reset('nom');
    }

    public function basculerRubrique(int $id): void
    {
        $rubrique = $this->rubrique($id);
        $rubrique->update(['is_published' => ! $rubrique->is_published]);
    }

    public function supprimerRubrique(int $id): void
    {
        $rubrique = $this->rubrique($id);

        DB::transaction(function () use ($rubrique) {
            $rubrique->articles()->delete();
            $rubrique->delete();
        });
    }

    public function creerPage(int $rubriqueId): void
    {
        $titre = trim($this->nouvellePage[$rubriqueId] ?? '');

        if ($titre === '' || mb_strlen($titre) > 255) {
            $this->addError('nouvellePage.'.$rubriqueId, __('Donnez un titre à la page.'));

            return;
        }

        $rubrique = $this->rubrique($rubriqueId);
        $page = $rubrique->articles()->create([
            'user_id' => Auth::id(),
            'title' => $titre,
            'slug' => Str::slug($titre),
            'status' => 'draft',
            'position' => (int) $rubrique->articles()->max('position') + 1,
        ]);

        if ($rubrique->page_order) {
            $rubrique->update(['page_order' => [...$rubrique->page_order, (string) $page->id]]);
        }

        unset($this->nouvellePage[$rubriqueId]);
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
        $this->corps = (string) $page->body;
        $this->blocs = $page->body_blocks ?? [];
        $this->resetErrorBag(['corps', 'blocs']);
    }

    public function enregistrerPage(NettoyeurHtml $nettoyeur): void
    {
        $this->validate(['corps' => 'nullable|string|max:200000']);

        $this->page($this->edition)->update(['body' => $nettoyeur->nettoyer($this->corps)]);
    }

    /** Enregistrement pour config('pages.editeur_texte') === 'redactor_bloc' : voir resources/js/espace-blocs.js. */
    public function enregistrerBlocs(RenduBlocsPage $rendu, NettoyeurHtml $nettoyeur): void
    {
        $this->validate(['blocs' => 'array']);

        $this->page($this->edition)->update([
            'body_blocks' => $this->blocs,
            'body' => $nettoyeur->nettoyer($rendu->versHtml($this->blocs)),
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
        $rubriques = Auth::user()->sections()->with('articles')->orderBy('kind')->orderBy('position')->get();

        foreach ($rubriques as $rubrique) {
            $ordre = array_flip($rubrique->page_order ?? []);
            $rubrique->setRelation('articles', $rubrique->articles->sortBy(
                fn (BookArticle $p) => [$ordre[(string) ($p->legacy_id ?? $p->id)] ?? PHP_INT_MAX, $p->legacy_id ?? $p->id]
            )->values());
        }

        return view('livewire.espace.pages', [
            'rubriques' => $rubriques,
            'editeurTexte' => config('pages.editeur_texte'),
        ]);
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
            'rubrique' => $this->rubrique((int) $id)->update(['title' => $valeur]),
            'page' => $this->page((int) $id)->update(['title' => $valeur, 'slug' => Str::slug($valeur) ?: 'page']),
        };
    }

    private function rubrique(int $id): BookSection
    {
        $rubrique = BookSection::findOrFail($id);
        $this->authorize('update', $rubrique);

        return $rubrique;
    }
}
