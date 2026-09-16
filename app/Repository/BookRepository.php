<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
    ): Collection {
        return $this->baseQuery($brand)
            ->when($categorySlug && $categorySlug !== 'all',
                fn (Builder $query) => $query->whereRelation('category', 'slug', $categorySlug))
            ->when($selection === 'ult', fn (Builder $query) => $query->where('is_selected', true))
            ->when($selection === 'lub', fn (Builder $query) => $query->where('plan', '>', 0))
            ->orderByDesc('is_selected')
            ->orderByDesc('media_count')
            ->skip($page * self::PER_PAGE)
            ->take(self::PER_PAGE)
            ->get();
    }

    public function count(?string $categorySlug = null, string $brand = 'ub'): int
    {
        return $this->baseQuery($brand)
            ->when($categorySlug && $categorySlug !== 'all',
                fn (Builder $query) => $query->whereRelation('category', 'slug', $categorySlug))
            ->count();
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
