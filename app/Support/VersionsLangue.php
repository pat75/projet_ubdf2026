<?php

namespace App\Support;

use App\Models\CmsPage;
use Illuminate\Support\Facades\Route;

/**
 * Liens hreflang d'une page du portail vers ses versions dans les autres
 * langues de la marque (Dustfolio : `en.categorie` <-> `fr.categorie`).
 *
 * Sans eux, Google traite /en/illustrator et /fr/illustrateur comme deux
 * pages concurrentes au lieu de deux versions d'une meme page. Ultra-book,
 * monolingue, n'en a pas besoin.
 */
final class VersionsLangue
{
    /**
     * @return array<string, string> code de langue (ou « x-default ») => URL absolue
     */
    public static function pour(Marque $marque): array
    {
        $racine = rtrim($marque->canonique, '/');
        $versions = array_map(fn (string $chemin) => $racine.$chemin, self::chemins($marque));

        if (count($versions) < 2) {
            return [];
        }

        $versions['x-default'] = $versions[$marque->locale()] ?? reset($versions);

        return $versions;
    }

    /**
     * Chemins relatifs de la page courante dans chaque langue servie ou
     * elle existe. Sert aussi au selecteur de langue, qui reste sur l'hote
     * courant (le domaine canonique n'est pas celui du developpement).
     *
     * @return array<string, string> code de langue => chemin (/fr/illustrateur)
     */
    public static function chemins(Marque $marque): array
    {
        $route = request()->route();
        $nom = $route?->getName();

        if (! $marque->multilingue() || ! $nom || ! preg_match('/^([a-z]{2})\.(.+)$/', $nom, $m)) {
            return [];
        }

        [, $langueCourante, $base] = $m;
        $parametres = $route->parameters();
        $versions = [];

        foreach ($marque->langues as $langue) {
            if (! Route::has($langue.'.'.$base)) {
                continue;
            }

            $params = $parametres;

            // Page metier : le segment change avec la langue (illustrator / illustrateur).
            if ($base === 'categorie' && isset($params['categorie'])) {
                $params['categorie'] = Metier::slugUrl(Metier::depuisSlugUrl($params['categorie']), $langue);
            }

            // Une page statique change de slug d'une langue a l'autre
            // (conditions-dutilisations / conditions-of-use) : on suit son
            // groupe de traduction, et on l'omet si elle n'est pas traduite.
            if (in_array($base, ['cms.doc', 'cms.page'], true) && isset($params['slug'])) {
                $slug = self::slugTraduit($params['slug'], $langueCourante, $langue);
                if ($slug === null) {
                    continue;
                }
                $params['slug'] = $slug;
            }

            $versions[$langue] = parse_url(route($langue.'.'.$base, $params, false), PHP_URL_PATH);
        }

        return $versions;
    }

    private static function slugTraduit(string $slug, string $depuis, string $vers): ?string
    {
        if ($depuis === $vers) {
            return $slug;
        }

        $groupe = CmsPage::query()->where('slug', $slug)->where('locale', $depuis)->value('translation_group')
            ?? CmsPage::query()->where('slug', $slug)->value('translation_group');

        if (! $groupe) {
            return null;
        }

        return CmsPage::publiees()->where('translation_group', $groupe)->where('locale', $vers)->value('slug');
    }
}
