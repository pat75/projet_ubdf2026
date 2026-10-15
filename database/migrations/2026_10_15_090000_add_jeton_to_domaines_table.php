<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domaine deja possede par le createur (source externe) : jeton de
 * l'enregistrement TXT qui prouve qu'il en a la main.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domaines', function (Blueprint $table) {
            $table->string('jeton', 40)->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('domaines', function (Blueprint $table) {
            $table->dropColumn('jeton');
        });
    }
};
