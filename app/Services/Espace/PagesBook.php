<?php

namespace App\Services\Espace;

use App\Models\BookArticle;
use App\Models\BookSection;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** Rubriques et pages (bio) d'un creatif : partage par l'espace (Livewire Pages) et l'API. */
class PagesBook
{
    public function __construct(private readonly NettoyeurHtml $nettoyeur) {}

    /**
     * Les rubriques, leurs pages dans l'ordre du book (`page_order`, en
     * identifiants legacy pour les pages reprises).
     */
    public function rubriques(User $creatif): Collection
    {
        $rubriques = $creatif->sections()->with('articles')->orderBy('position')->orderBy('id')->get();

        foreach ($rubriques as $rubrique) {
            $ordre = array_flip($rubrique->page_order ?? []);
            $rubrique->setRelation('articles', $rubrique->articles->sortBy(
                fn (BookArticle $p) => [$ordre[(string) ($p->legacy_id ?? $p->id)] ?? PHP_INT_MAX, $p->legacy_id ?? $p->id]
            )->values());
        }

        return $rubriques;
    }

    public function creerRubrique(User $creatif, string $titre = ''): BookSection
    {
        // En tete de liste : position non signee, on decale les autres
        // plutot que de descendre sous 0.
        $creatif->sections()->increment('position');

        $rubrique = $creatif->sections()->create([
            'kind' => BookSection::PAGES,
            'title' => '',
            'slug' => 'rubrique',
            'is_published' => false,
            'position' => 0,
        ]);

        if ($titre !== '') {
            $this->nommerRubrique($rubrique, $titre);
        }

        return $rubrique;
    }

    /** Une rubrique nommee pour la premiere fois est publiee d'office. */
    public function nommerRubrique(BookSection $rubrique, string $titre): void
    {
        $premiereFois = $rubrique->title === '';

        $rubrique->update([
            'title' => $titre,
            'slug' => Str::slug($titre) ?: 'rubrique',
            'is_published' => $premiereFois ? true : $rubrique->is_published,
        ]);
    }

    /** Une rubrique ne se supprime que vide : ses pages d'abord. Rend false si elle ne l'est pas. */
    public function supprimerRubrique(BookSection $rubrique): bool
    {
        if ($rubrique->articles()->exists()) {
            return false;
        }

        $rubrique->delete();

        return true;
    }

    /** Nouvelle page en brouillon, en fin de rubrique. */
    public function creerPage(BookSection $rubrique, string $titre = ''): BookArticle
    {
        $page = $rubrique->articles()->create([
            'user_id' => $rubrique->user_id,
            'title' => '',
            'slug' => 'page',
            'status' => 'draft',
            'position' => (int) $rubrique->articles()->max('position') + 1,
        ]);

        if ($rubrique->page_order) {
            $rubrique->update(['page_order' => [...$rubrique->page_order, (string) $page->id]]);
        }

        if ($titre !== '') {
            $this->nommerPage($page, $titre);
        }

        return $page;
    }

    public function nommerPage(BookArticle $page, string $titre): void
    {
        $page->update(['title' => $titre, 'slug' => Str::slug($titre) ?: 'page']);
    }

    /**
     * Enregistre le HTML d'une page, nettoye, et la publie.
     *
     * @param  array<mixed>|null  $blocs  editeur par blocs ; null efface les blocs (HTML seul fait foi)
     */
    public function enregistrer(BookArticle $page, string $html, ?array $blocs = null): void
    {
        $page->update(['body' => $this->nettoyeur->nettoyer($html), 'body_blocks' => $blocs, 'status' => 'published']);
    }

    /** @param  list<int|string>  $ids */
    public function ordonnerPages(BookSection $rubrique, array $ids): void
    {
        $pages = $rubrique->articles()->whereIn('id', $ids)->get()->keyBy('id');

        $rubrique->update(['page_order' => collect($ids)
            ->map(fn ($id) => $pages->get((int) $id))->filter()
            ->map(fn (BookArticle $p) => (string) ($p->legacy_id ?? $p->id))
            ->values()->all()]);
    }
}
