<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contenu structure de l'editeur par blocs (Editor.js), en plus du HTML
 * de `body` : config('pages.editeur_texte') choisit lequel des deux
 * moteurs edite la page, mais les deux colonnes restent synchronisees a
 * chaque enregistrement (App\Services\Espace\RenduBlocsPage), pour qu'un
 * futur affichage public lise l'une ou l'autre sans distinction.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_articles', function (Blueprint $table) {
            $table->json('body_blocks')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('book_articles', function (Blueprint $table) {
            $table->dropColumn('body_blocks');
        });
    }
};
