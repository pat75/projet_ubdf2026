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

    protected $fillable = ['q', 'brand'];

    /** Premiere page d'une recherche par mots-cles exploitable seulement. */
    public static function journaliser(Recherche $recherche): void
    {
        if ($recherche->mode !== 'mcles' || $recherche->page > 0 || ! $recherche->exploitable()) {
            return;
        }

        static::create([
            'q' => mb_substr(mb_strtolower($recherche->q), 0, 191),
            'brand' => $recherche->brand,
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
