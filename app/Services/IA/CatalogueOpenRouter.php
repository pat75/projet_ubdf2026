<?php

namespace App\Services\IA;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Catalogue public des modeles OpenRouter (GET /models, sans cle ni
 * token consomme), pour verifier que les modeles choisis existent
 * toujours et acceptent les images quand il le faut.
 */
class CatalogueOpenRouter
{
    private const URL = 'https://openrouter.ai/api/v1/models';

    private const CACHE = 'openrouter_catalogue_v2';

    /** Mots qui ne disent rien de la famille d'un modele. */
    private const MOTS_NEUTRES = ['preview', 'exp', 'latest', 'free', 'batch', 'instruct', 'chat', 'it'];

    /**
     * Modeles indexes par id. Vide si OpenRouter ne repond pas (et rien
     * n'est alors mis en cache).
     *
     * @return array<string, array{vision: bool, prix: ?string, entree: ?float}>
     */
    public function modeles(): array
    {
        if ($modeles = Cache::get(self::CACHE)) {
            return $modeles;
        }

        try {
            $donnees = Http::timeout(15)->get(self::URL)->throw()->json('data') ?? [];
        } catch (Throwable) {
            return [];
        }

        $modeles = [];
        foreach ($donnees as $m) {
            if (! isset($m['id'])) {
                continue;
            }
            $modeles[$m['id']] = [
                'vision' => in_array('image', $m['architecture']['input_modalities'] ?? [], true),
                'prix' => self::prix($m['pricing'] ?? []),
                'entree' => isset($m['pricing']['prompt']) ? (float) $m['pricing']['prompt'] * 1_000_000 : null,
            ];
        }

        if ($modeles) {
            Cache::put(self::CACHE, $modeles, 3600);
        }

        return $modeles;
    }

    public function oublier(): void
    {
        Cache::forget(self::CACHE);
    }

    /**
     * `valide` null : catalogue injoignable, rien n'est conclu.
     *
     * @return array{valide: ?bool, motif: ?string, prix: ?string}
     */
    public function etat(string $id, string $capacite): array
    {
        $catalogue = $this->modeles();

        if (! $catalogue) {
            return ['valide' => null, 'motif' => 'Catalogue OpenRouter injoignable', 'prix' => null];
        }
        if (! isset($catalogue[$id])) {
            return ['valide' => false, 'motif' => 'Absent du catalogue (retiré ou renommé)', 'prix' => null];
        }
        if ($capacite === 'vision' && ! $catalogue[$id]['vision']) {
            return ['valide' => false, 'motif' => 'N’accepte pas les images', 'prix' => $catalogue[$id]['prix']];
        }

        return ['valide' => true, 'motif' => null, 'prix' => $catalogue[$id]['prix']];
    }

    /**
     * Remplacant d'un modele retire ou renomme : meme fournisseur, meme
     * capacite, famille la plus proche (mots du nom : « haiku »,
     * « flash-lite »…), puis prix d'entree le plus proche de $prixReference
     * ($/M tokens, celui des autres modeles du niveau), puis le plus recent
     * (le catalogue est trie du plus recent au plus ancien).
     *
     * @param  string[]  $exclus  modeles deja dans la liste
     */
    public function suggestion(string $id, string $capacite, array $exclus = [], ?float $prixReference = null): ?string
    {
        [$fournisseur] = explode('/', $id, 2);
        $mots = self::mots($id);
        $rang = 0;

        return collect($this->modeles())
            ->map(fn (array $m, string $cle) => $m + ['id' => $cle, 'rang' => $rang++])
            ->filter(fn (array $m) => str_starts_with($m['id'], $fournisseur.'/')
                && ! str_contains($m['id'], ':')
                && ! in_array($m['id'], $exclus, true)
                && ($capacite !== 'vision' || $m['vision']))
            ->map(function (array $m) use ($mots, $prixReference) {
                $autres = self::mots($m['id']);
                $union = count(array_unique([...$mots, ...$autres]));
                $m['proximite'] = $union ? count(array_intersect($mots, $autres)) / $union : 0;
                // Ecart de prix en ordre de grandeur : x2 ou /2 pesent pareil.
                $m['ecart'] = $prixReference && $m['entree']
                    ? abs(log($m['entree'] / $prixReference))
                    : 0;

                return $m;
            })
            ->filter(fn (array $m) => $m['proximite'] > 0)
            ->sortBy([['proximite', 'desc'], ['ecart', 'asc'], ['rang', 'asc']])
            ->first()['id'] ?? null;
    }

    /** @return string[] mots alphabetiques du nom, hors fournisseur et mots neutres */
    private static function mots(string $id): array
    {
        $nom = str_contains($id, '/') ? explode('/', $id, 2)[1] : $id;

        return array_values(array_diff(
            array_filter(preg_split('/[^a-z]+/', strtolower($nom))),
            self::MOTS_NEUTRES,
        ));
    }

    /** Prix entree / sortie en dollars par million de tokens. */
    private static function prix(array $pricing): ?string
    {
        if (! isset($pricing['prompt'], $pricing['completion'])) {
            return null;
        }
        $entree = (float) $pricing['prompt'] * 1_000_000;
        $sortie = (float) $pricing['completion'] * 1_000_000;

        return $entree == 0 && $sortie == 0
            ? 'gratuit'
            : sprintf('$%s / $%s par M tokens', round($entree, 2), round($sortie, 2));
    }
}
