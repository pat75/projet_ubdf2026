<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Date de mise en selection sur la page d'accueil.
 *
 * Le legacy n'avait qu'un booleen (`us_affhome`) : on savait qu'un book
 * etait en selection, jamais depuis quand, et l'ordre d'affichage tenait
 * au seul nombre de visuels. La date permet de remonter les entrees
 * recentes et de dater une selection a posteriori.
 *
 * Elle ne conditionne pas la visibilite : c'est toujours le booleen qui
 * decide, la date ne fait qu'ordonner et documenter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('home_selection_at')->nullable()->after('in_home_selection')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('home_selection_at');
        });
    }
};
