<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ordre des pages d'une rubrique, repris de `ub2_gal_rub.rub_ordre_img`.
 *
 * Meme mecanisme que `galleries.media_order` : le legacy n'a pas de colonne
 * de position sur les pages, l'ordre est une liste d'identifiants portee
 * par la rubrique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_sections', function (Blueprint $table) {
            $table->json('page_order')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('book_sections', function (Blueprint $table) {
            $table->dropColumn('page_order');
        });
    }
};
