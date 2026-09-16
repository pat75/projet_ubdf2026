<?php

namespace App\Repository;

use App\Models\User;
use App\Support\Recherche;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Selection des books affiches sur le portail.
 *
 * Reprend les trois modes de tri du front 2018, identifies dans les URL du
 * legacy par un code de trois lettres :
 *   sel — selection editoriale du moment
 *   ult — ultra-selection (comptes distingues)
 *   lub — les ultra-books (comptes abonnes)
 */
class BookRepository
{
    public const PER_PAGE = 10;

    /**
     * @return Collection<int, User>
     */
    public function portfolios(
        string $selection = 'sel',
        ?string $categorySlug = null,
        int $page = 0,
        string $brand = 'ub',
        ?int $perPage = null,
    ): Collection {
        $perPage ??= self::PER_PAGE;

        return $this->baseQuery($brand)
            ->when($categorySlug && $categorySlug !== 'all',
                fn (Builder $query) => $query->whereRelation('category', 'slug', $categorySlug))
            ->when($selection === 'ult', fn (Builder $query) => $query->where('is_selected', true))
            ->when($selection === 'lub', fn (Builder $query) => $query->where('plan', '>', 0))
            ->orderByDesc('is_selected')
            ->orderByDesc('media_count')
            ->skip($page * $perPage)
            ->take($perPage)
            ->get();
    }

    /**
     * Nombre de books par categorie, en une requete.
     *
     * Affiche sur la derniere carte de chaque bloc de l'accueil
     * (« 13 164 illustrateurs »).
     *
     * @return array<string, int>
     */
    public function countsByCategory(string $brand = 'ub'): array
    {
        return $this->baseQuery($brand)
            ->join('categories', 'categories.id', '=', 'users.category_id')
            ->groupBy('categories.slug')
            ->pluck(DB::raw('COUNT(*)'), 'categories.slug')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    public function count(?string $categorySlug = null, string $brand = 'ub'): int
    {
        return $this->baseQuery($brand)
            ->when($categorySlug && $categorySlug !== 'all',
                fn (Builder $query) => $query->whereRelation('category', 'slug', $categorySlug))
            ->count();
    }


    /**
     * Recherche du portail.
     *
     * Deux modes, ceux du front 2018 :
     *   mcles  — sur les mots-cles declares par le creatif
     *   pseudo — sur le login, le prenom et le nom
     *
     * Ecart assume avec le legacy sur le mode « mcles » : celui-ci exigeait
     * les deux premiers termes (AND) et ignorait les suivants, ce qui rendait
     * une requete de trois mots presque toujours vide. Ici un book ressort
     * des qu'il porte **un** des termes, et ceux qui en portent le plus
     * remontent en tete.
     *
     * @return Collection<int, User>
     */
    public function rechercher(Recherche $recherche): Collection
    {
        $query = $this->requeteRecherche($recherche);

        if (! $query instanceof Builder) {
            return new Collection;
        }

        return $query
            ->orderByDesc('pertinence')
            ->orderByDesc('users.is_selected')
            ->orderByDesc('users.media_count')
            ->skip($recherche->page * self::PER_PAGE)
            ->take(self::PER_PAGE)
            ->get();
    }

    public function compterRecherche(Recherche $recherche): int
    {
        $query = $this->requeteRecherche($recherche);

        // Le tri par pertinence n'a pas de sens ici, et MySQL refuse un
        // COUNT sur une selection qui porte des parametres lies.
        return $query instanceof Builder ? $query->reorder()->getQuery()->getCountForPagination() : 0;
    }

    /**
     * Requete commune au comptage et a la liste, ou null si la saisie ne
     * contient aucun terme exploitable.
     */
    private function requeteRecherche(Recherche $recherche): ?Builder
    {
        $termes = $recherche->termes();

        if ($termes === []) {
            return null;
        }

        // Colonnes interrogees selon le mode ; la pertinence est le nombre
        // de termes trouves, calculee par la base pour ne pas avoir a
        // rendre la requete une seconde fois en PHP.
        $colonnes = $recherche->mode === 'pseudo'
            ? ['users.login', 'users.firstname', 'users.lastname']
            : ['book_settings.keywords'];

        $query = $this->baseQuery($recherche->brand)
            ->when($recherche->mode !== 'pseudo', fn (Builder $q) => $q->join(
                'book_settings', 'book_settings.user_id', '=', 'users.id'
            ))
            ->when($recherche->categorie && $recherche->categorie !== 'tous',
                fn (Builder $q) => $q->whereRelation('category', 'slug', $recherche->categorie))
            ->when($recherche->selection, fn (Builder $q) => $q->where('users.is_selected', true))
            ->when($recherche->abonnes, fn (Builder $q) => $q->where('users.plan', '>', 0))
            ->select('users.*');

        $pertinence = [];
        $valeurs = [];

        foreach ($termes as $terme) {
            $motif = '%'.$this->echapper($terme).'%';

            $trouve = implode(' OR ', array_map(fn ($colonne) => "{$colonne} LIKE ?", $colonnes));
            $pertinence[] = "({$trouve})";

            foreach ($colonnes as $ignore) {
                $valeurs[] = $motif;
            }
        }

        $conditions = implode(' + ', $pertinence);

        return $query
            ->selectRaw("({$conditions}) AS pertinence", $valeurs)
            ->whereRaw("({$conditions}) > 0", $valeurs);
    }

    /** Neutralise les jokers de LIKE saisis par l'utilisateur. */
    private function echapper(string $terme): string
    {
        return addcslashes($terme, '%_\\');
    }

    /** Books visibles : publies, en annuaire, et pourvus d'au moins un visuel. */
    private function baseQuery(string $brand): Builder
    {
        return User::query()
            ->with([
                'category',
                'bookSetting',
                'media' => fn ($query) => $query->published()->whereNot('filename', '')->orderBy('position')->limit(6),
            ])
            ->where('brand', $brand)
            ->where('is_published', true)
            ->whereHas('media', fn (Builder $query) => $query->published()->whereNot('filename', ''));
    }
}
