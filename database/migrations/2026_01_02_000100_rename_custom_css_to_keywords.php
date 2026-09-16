<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `inc_user_pref.us_pf_css` ne contient pas de CSS.
 *
 * Le nom est trompeur : la colonne stocke les mots-cles du book, et c'est
 * sur elle que porte la recherche du front 2018 :
 *
 *     WHERE user_pref.us_pf_css LIKE '%illustration%'
 *
 * Le contenu reel le confirme (« Brochures,Affiches,Flyers,Logos »,
 * « #fashion, #chanel, #nyc »). La colonne est renommee pour dire ce
 * qu'elle contient.
 *
 * Pas d'index FULLTEXT : la recherche du portail reste un LIKE, comme dans
 * le legacy — les mots-cles sont choisis dans une liste fermee, mais la
 * saisie libre doit continuer a trouver un prefixe (« illustr »), ce qu'un
 * index en texte integral ne fait pas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_settings', function (Blueprint $table) {
            $table->renameColumn('custom_css', 'keywords');
        });
    }

    public function down(): void
    {
        Schema::table('book_settings', function (Blueprint $table) {
            $table->renameColumn('keywords', 'custom_css');
        });
    }
};
