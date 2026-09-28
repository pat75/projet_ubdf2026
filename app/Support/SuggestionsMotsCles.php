<?php

namespace App\Support;

use App\Models\BookSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Auto-completion du moteur de recherche d'apres les mots-cles que les
 * createurs ont reellement saisis (`book_settings.keywords`), et non plus
 * seulement la liste figee de motcles.json.
 *
 * L'index (mot => nombre de books diffuses qui le portent) est calcule une
 * fois par heure : la colonne n'est pas indexable pour une recherche par
 * prefixe, et la table compte des dizaines de milliers de lignes.
 */
final class SuggestionsMotsCles
{
    public const LONGUEUR_MIN = 3;

    private const CLE_CACHE = 'recherche.index-mots-cles';

    /**
     * Mots-cles qui commencent par la saisie (ou dont un mot commence par
     * elle), les plus portes en tete.
     *
     * @return list<array{mot: string, total: int}>
     */
    public static function pour(string $saisie, int $limite = 10): array
    {
        $saisie = self::plier($saisie);
        if (mb_strlen($saisie) < self::LONGUEUR_MIN) {
            return [];
        }

        $debut = [];
        $milieu = [];
        foreach (self::index() as $mot => $total) {
            $plie = self::plier((string) $mot);
            if (str_starts_with($plie, $saisie)) {
                $debut[] = ['mot' => (string) $mot, 'total' => $total];
            } elseif (str_contains($plie, ' '.$saisie)) {
                $milieu[] = ['mot' => (string) $mot, 'total' => $total];
            }
        }

        // L'index est deja trie par frequence : l'ordre est conserve.
        return array_slice([...$debut, ...$milieu], 0, $limite);
    }

    /** @return array<string, int> mot => nombre de books, du plus porte au moins porte */
    public static function index(): array
    {
        return Cache::remember(self::CLE_CACHE, now()->addHour(), function () {
            $compte = [];

            BookSetting::query()
                ->whereNotNull('keywords')
                ->where('diffuse_web', true)
                ->where('diffuse_ub', true)
                ->select(['id', 'keywords'])
                ->chunkById(2000, function ($reglages) use (&$compte) {
                    foreach ($reglages as $reglage) {
                        foreach (array_unique(array_map('mb_strtolower', MotsCles::decouper($reglage->keywords))) as $mot) {
                            $compte[$mot] = ($compte[$mot] ?? 0) + 1;
                        }
                    }
                });

            arsort($compte);

            return $compte;
        });
    }

    public static function oublier(): void
    {
        Cache::forget(self::CLE_CACHE);
    }

    /** Comparaison insensible a la casse et aux accents. */
    private static function plier(string $texte): string
    {
        return mb_strtolower(Str::ascii(trim($texte)));
    }
}
