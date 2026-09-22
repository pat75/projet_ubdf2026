<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Separe les pages de texte des galeries.
 *
 * Le legacy range tout dans `ub2_gal_rub`, distingue par
 * `rub_id_categorie` : 1 = pages d'accueil, 2 = portfolio, 3 = pages
 * (bio, actualites). Les rubriques 1 et 3 ne portent aucun visuel, mais
 * des pages dont le contenu est dans `ub2_gal_img.img_html`, sans fichier.
 *
 * L'import initial n'en tenait pas compte : il reprenait toutes les
 * rubriques comme galeries et ecartait les pages comme « visuels sans
 * fichier ». Sur l'echantillon, 438 pages de texte etaient ainsi perdues
 * — la page Bio de chaque book en theme 2012 et suivants.
 *
 * Ces pages rejoignent `book_sections` / `book_articles`, qui recevaient
 * deja les actualites du theme classique 2010 (`bn_ultranews_*`). Les deux
 * origines partageant les tables, l'identifiant legacy n'est plus unique
 * seul : il l'est avec sa source.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_sections', function (Blueprint $table) {
            // news : bn_ultranews_rub (classique 2010)
            // accueil, pages : ub2_gal_rub categories 1 et 3 (themes 2012+)
            $table->string('kind', 12)->default('news')->after('parent_id');
            $table->string('legacy_source', 20)->nullable()->after('legacy_id');

            $table->dropUnique(['legacy_id']);
            $table->unique(['legacy_source', 'legacy_id']);
            $table->index(['user_id', 'kind', 'position']);
        });

        Schema::table('book_articles', function (Blueprint $table) {
            $table->string('legacy_source', 20)->nullable()->after('legacy_id');

            $table->dropUnique(['legacy_id']);
            $table->unique(['legacy_source', 'legacy_id']);
        });

        Schema::table('book_settings', function (Blueprint $table) {
            /*
             | Blocs de texte editables en place, par theme (`ub2_edit_txt`) :
             | { "mdl_2016_zoom": { "cont_menu_gauche2": "<p>…</p>", … } }.
             | Le legacy les range par couple (login, theme) : un createur
             | qui change de theme retrouve ses textes en revenant.
             */
            $table->json('theme_texts')->nullable()->after('theme_settings');
        });

        DB::table('book_sections')->whereNotNull('legacy_id')->update(['legacy_source' => 'bn_ultranews_rub']);
        DB::table('book_articles')->whereNotNull('legacy_id')->update(['legacy_source' => 'bn_ultranews_art']);
    }

    public function down(): void
    {
        Schema::table('book_settings', function (Blueprint $table) {
            $table->dropColumn('theme_texts');
        });

        Schema::table('book_articles', function (Blueprint $table) {
            $table->dropUnique(['legacy_source', 'legacy_id']);
            $table->dropColumn('legacy_source');
            $table->unique(['legacy_id']);
        });

        Schema::table('book_sections', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'kind', 'position']);
            $table->dropUnique(['legacy_source', 'legacy_id']);
            $table->dropColumn(['kind', 'legacy_source']);
            $table->unique(['legacy_id']);
        });
    }
};
