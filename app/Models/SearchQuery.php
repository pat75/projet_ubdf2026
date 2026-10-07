<?php

namespace App\Models;

use App\Support\Recherche;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Une recherche par mots-cles lancee sur le portail.
 */
class SearchQuery extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['q', 'brand', 'nb_books', 'nb_images', 'result_user_ids'];

    protected function casts(): array
    {
        return ['result_user_ids' => 'array'];
    }

    /** Premiere page d'une recherche par mots-cles exploitable seulement. */
    /** @param  list<int>  $creatifs  ids des creatifs renvoyes (books et images) */
    public static function journaliser(Recherche $recherche, int $nbBooks = 0, int $nbImages = 0, array $creatifs = []): void
    {
        if ($recherche->mode !== 'mcles' || $recherche->page > 0 || ! $recherche->exploitable()) {
            return;
        }

        static::create([
            'q' => mb_substr(mb_strtolower($recherche->q), 0, 191),
            'brand' => $recherche->brand,
            'nb_books' => $nbBooks,
            'nb_images' => $nbImages,
            'result_user_ids' => $creatifs,
        ]);
    }

    /**
     * Les requetes les plus frequentes sur la periode, la plus recherchee
     * en tete.
     *
     * @return Collection<int, string>
     */
    public static function populaires(string $brand, int $jours = 90, int $limite = 24): Collection
    {
        return static::query()
            ->where('brand', $brand)
            ->where('created_at', '>=', now()->subDays($jours))
            ->selectRaw('q, COUNT(*) as total')
            ->groupBy('q')
            ->orderByDesc('total')
            ->orderBy('q')
            ->limit($limite)
            ->pluck('q');
    }
}
