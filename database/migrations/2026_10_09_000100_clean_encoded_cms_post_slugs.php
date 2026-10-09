<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Slugs d'actualites encodes par WordPress (« d%e2%80%99illustrateurs ») :
 * aucune route ne les servait, d'ou 5 adresses en 404 dans le sitemap. Ils
 * prennent la forme des autres slugs ; l'ancienne adresse est redirigee en
 * 301 par CmsController::actualite.
 */
return new class extends Migration
{
    private const SLUGS = [
        '%e2%80%9catelier-de-signes%e2%80%9d' => 'atelier-de-signes',
        'invitez-vous-sur-le-blog-d%e2%80%99ultra-book' => 'invitez-vous-sur-le-blog-dultra-book',
        'sine-hebdo-deja-un-an-et-presque-3-millions-d%e2%80%99exemplaires' => 'sine-hebdo-deja-un-an-et-presque-3-millions-dexemplaires',
        'trois-villes-et-trois-actualites-d%e2%80%99illustrateurs' => 'trois-villes-et-trois-actualites-dillustrateurs',
        'vous-avez-dit-%e2%80%9cfree-pitchings%e2%80%9d' => 'vous-avez-dit-free-pitchings',
    ];

    public function up(): void
    {
        foreach (self::SLUGS as $ancien => $nouveau) {
            DB::table('cms_posts')->where('slug', $ancien)->update(['slug' => $nouveau]);
        }
    }

    public function down(): void
    {
        foreach (self::SLUGS as $ancien => $nouveau) {
            DB::table('cms_posts')->where('slug', $nouveau)->update(['slug' => $ancien]);
        }
    }
};
