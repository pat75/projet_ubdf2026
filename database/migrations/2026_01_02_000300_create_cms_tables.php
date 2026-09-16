<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pages editoriales et actualites du portail.
 *
 * Le site de 2019 les servait en chargeant un WordPress entier dans le
 * processus du portail :
 *
 *     require('../magazine/wp-load.php');
 *     $tpl->wp_cont = new WP_Query(['pagename' => '/'.$page_wp]);
 *
 * Soit, pour 27 pages et 71 actualites, un second framework a demarrer a
 * chaque requete, ses tables, son cycle de mise a jour et sa surface
 * d'attaque — le tout dans une installation figee depuis 2018. Les contenus
 * sont repris ici ; `ubdf:import-cms` rejoue l'import a la demande.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();

            // Identifiant du groupe de traduction (trid de WPML) : relie la
            // page francaise a son equivalent anglais.
            $table->unsignedInteger('translation_group')->nullable()->index();

            $table->string('slug', 190);
            $table->string('locale', 5)->default('fr');
            $table->string('parent_slug', 190)->nullable()->index();

            $table->string('title');
            $table->longText('body')->nullable();
            $table->text('excerpt')->nullable();

            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamp('published_at')->nullable()->index();

            $table->timestamps();

            // Un meme slug existe dans les deux langues (« doc »).
            $table->unique(['slug', 'locale']);
        });

        Schema::create('cms_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();

            $table->string('slug', 190);
            $table->string('locale', 5)->default('fr');

            $table->string('title');
            $table->longText('body')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('image')->nullable();

            $table->timestamp('published_at')->nullable()->index();

            $table->timestamps();

            $table->unique(['slug', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_posts');
        Schema::dropIfExists('cms_pages');
    }
};
