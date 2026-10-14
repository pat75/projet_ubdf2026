<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Page propre a une marque. Vide : page commune, celle d'Ultra-book, que
 | Dustfolio sert avec le nom substitue (et en noindex, c'est une copie).
 | « df » : version ecrite pour Dustfolio, qui remplace la commune sur ce
 | domaine pour le meme slug et la meme langue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->string('marque', 4)->default('')->after('locale');
            $table->dropUnique(['slug', 'locale']);
            $table->unique(['slug', 'locale', 'marque']);
        });
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropUnique(['slug', 'locale', 'marque']);
            $table->unique(['slug', 'locale']);
            $table->dropColumn('marque');
        });
    }
};
