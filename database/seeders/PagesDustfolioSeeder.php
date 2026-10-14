<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Pages francaises propres a Dustfolio (database/contenus/dustfolio/fr).
 *
 * Chaque fichier remplace, sur Dustfolio, la page commune de meme slug :
 * premiere ligne en JSON (title, seo_title, excerpt), puis le corps HTML.
 * Place dans l'arbre (parent, position) et groupe de traduction sont repris
 * de la page commune. Rejouable : met a jour ce qui existe.
 */
class PagesDustfolioSeeder extends Seeder
{
    public function run(): void
    {
        foreach (File::files(database_path('contenus/dustfolio/fr')) as $fichier) {
            $slug = $fichier->getFilenameWithoutExtension();
            [$entete, $corps] = explode("\n", File::get($fichier->getPathname()), 2);
            $meta = json_decode($entete, true, flags: JSON_THROW_ON_ERROR);

            $commune = CmsPage::query()->where(['slug' => $slug, 'locale' => 'fr', 'marque' => ''])->first();

            CmsPage::query()->updateOrCreate(
                ['slug' => $slug, 'locale' => 'fr', 'marque' => 'df'],
                [
                    'title' => $meta['title'],
                    'seo_title' => $meta['seo_title'],
                    'excerpt' => $meta['excerpt'],
                    'body' => trim($corps),
                    'parent_slug' => $commune?->parent_slug,
                    'position' => $commune?->position ?? 0,
                    'translation_group' => $commune?->translation_group,
                    'published_at' => $commune?->published_at ?? now(),
                ],
            );
        }
    }
}
