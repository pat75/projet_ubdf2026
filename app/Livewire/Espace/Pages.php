<?php

namespace App\Livewire\Espace;

use App\Models\BookArticle;
use App\Models\BookSection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Rubriques de pages du book (bio, actualites, pages d'accueil) et leurs
 * pages : pag_add, rub_* de categorie 1 et 3 du legacy.
 */
class Pages extends Component
{
    #[Validate('required|string|max:100')]
    public string $nom = '';

    /** Titre de la nouvelle page, par rubrique. */
    public array $nouvellePage = [];

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

    public function renommer(int $id, string $nom): void
    {
        $nom = trim($nom);

        if ($nom === '' || mb_strlen($nom) > 100) {
            $this->addError('renommer.'.$id, __('Le nom doit faire entre 1 et 100 caractères.'));

            return;
        }

        $this->rubrique($id)->update(['title' => $nom]);
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

    public function creerPage(int $rubriqueId)
    {
        $titre = trim($this->nouvellePage[$rubriqueId] ?? '');

        if ($titre === '' || mb_strlen($titre) > 255) {
            $this->addError('nouvellePage.'.$rubriqueId, __('Donnez un titre à la page.'));

            return null;
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

        return $this->redirectRoute('espace.pages.edit', $page, navigate: false);
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

        return view('livewire.espace.pages', ['rubriques' => $rubriques]);
    }

    private function rubrique(int $id): BookSection
    {
        $rubrique = BookSection::findOrFail($id);
        $this->authorize('update', $rubrique);

        return $rubrique;
    }
}
