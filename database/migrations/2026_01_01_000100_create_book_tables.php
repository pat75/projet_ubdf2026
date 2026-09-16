<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reglages et theme du book. Remplace inc_user_pref, dont les ~20
        // colonnes de themes par millesime sont ramenees a theme + settings.
        Schema::create('book_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('description_mobile')->nullable();
            $table->text('experience')->nullable();
            $table->string('footer')->nullable();

            // Theme actif (slug normalise : mdl_2016_zoom, mdl_2015_grid, ...)
            $table->string('theme', 60)->default('mdl_default')->index();
            $table->json('theme_settings')->nullable();
            $table->string('theme_home_image')->nullable();

            // Apparence
            $table->string('background_image')->nullable();
            $table->string('background_color', 10)->nullable();
            $table->unsignedTinyInteger('background_mode')->nullable();
            $table->boolean('is_centered')->default(false);
            $table->string('thumbnail')->nullable();
            $table->string('bio_photo')->nullable();

            // Personnalisation avancee
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->string('analytics_id', 40)->nullable();

            // Diffusion
            $table->boolean('diffuse_ub')->default(false);
            $table->boolean('diffuse_web')->default(false);
            $table->boolean('diffuse_newsletter')->default(false);
            $table->boolean('diffuse_availability')->default(false);

            // Configuration des anciens themes, conservee telle quelle.
            $table->json('legacy_payload')->nullable();

            $table->timestamps();
        });

        // Rubriques / galeries du book (ub2_gal_rub).
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('galleries')->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('status', 12)->default('draft')->index();  // published|draft|deleted
            $table->unsignedInteger('position')->default(0);
            $table->string('color', 6)->nullable();

            // Ordre libre des medias (rub_ordre_img), sans table pivot.
            $table->json('media_order')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'position']);
        });

        // Visuels du book (ub2_gal_img).
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gallery_id')->nullable()->constrained()->nullOnDelete();

            $table->string('filename');
            $table->string('title')->nullable();
            $table->string('alt')->nullable();
            $table->string('link')->nullable();
            $table->text('description')->nullable();

            $table->string('mime', 60)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->string('status', 12)->default('draft')->index();
            $table->unsignedInteger('position')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['gallery_id', 'position']);
        });

        // Pages de contenu et actualites du book (bn_ultranews_*).
        Schema::create('book_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('book_sections')->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_private')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->string('color', 6)->nullable();
            $table->string('icon')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_published', 'position']);
        });

        Schema::create('book_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_section_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->nullable();
            $table->longText('body')->nullable();
            $table->string('image')->nullable();
            $table->text('keywords')->nullable();

            $table->string('status', 12)->default('draft')->index();  // published|draft|archived
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_articles');
        Schema::dropIfExists('book_sections');
        Schema::dropIfExists('media');
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('book_settings');
    }
};
