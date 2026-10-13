<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Photo du compte Google du visiteur (GoogleVisiteurController), affichee
| a la place des initiales. Simple adresse : rafraichie a chaque connexion.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('avatar_url', 500)->nullable()->after('google_id');
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn('avatar_url');
        });
    }
};
