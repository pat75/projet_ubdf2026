<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Titre de la balise <title>, distinct du h1 : le h1 reste court et lisible
 | dans le sommaire, le <title> porte la requete visee (« Comment créer son
 | book… »). Vide : le <title> reprend le titre de la page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->string('seo_title', 190)->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn('seo_title');
        });
    }
};
