<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Relances avant l'echeance d'un nom de domaine (J-30, J-15, J-5) :
 | nombre envoye pour l'echeance en cours, date de la derniere. Voir
 | App\Console\Commands\RelancerDomainesCommand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domaines', function (Blueprint $table) {
            $table->unsignedTinyInteger('relances_envoyees')->default(0);
            $table->timestamp('derniere_relance_le')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('domaines', function (Blueprint $table) {
            $table->dropColumn(['relances_envoyees', 'derniere_relance_le']);
        });
    }
};
