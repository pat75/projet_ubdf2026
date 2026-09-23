<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Surface d'ou vient la visite : le book, le mini-book, le memo-book ou
 * le micro-book.
 *
 * Le tableau de bord d'origine affichait cette repartition en anneau,
 * mais les chiffres venaient d'un serveur exterieur (extra-book.com) que
 * la refonte ne reprend pas. La dimension manquait donc ici : tout etait
 * agrege sous une seule valeur.
 *
 * Les lignes deja en base sont des vues de book — c'est la seule surface
 * que le pixel de comptage servait jusqu'ici.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('visit_stats', 'surface')) {
            Schema::table('visit_stats', function (Blueprint $table) {
                $table->string('surface', 12)->default('book')->after('date');
            });
        }

        /*
         | La cle d'unicite porte desormais sur le triplet : un meme book
         | peut avoir plusieurs lignes le meme jour, une par surface.
         |
         | L'ordre compte : la cle etrangere sur `user_id` s'appuie sur
         | l'index existant, et MySQL refuse de le supprimer tant qu'il
         | n'a pas d'autre index a sa disposition (erreur 1553). On pose
         | donc le nouveau avant de retirer l'ancien.
         */
        Schema::table('visit_stats', function (Blueprint $table) {
            $table->unique(['user_id', 'date', 'surface']);
        });

        Schema::table('visit_stats', function (Blueprint $table) {
            $table->dropUnique('visit_stats_user_id_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('visit_stats', function (Blueprint $table) {
            $table->unique(['user_id', 'date']);
        });

        Schema::table('visit_stats', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'date', 'surface']);
            $table->dropColumn('surface');
        });
    }
};
