<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            // Mot de passe demande aux visiteurs du book. Chiffre (cast
            // `encrypted`) et non hache : le createur doit pouvoir le relire
            // pour le transmettre a un client.
            $table->text('password')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
