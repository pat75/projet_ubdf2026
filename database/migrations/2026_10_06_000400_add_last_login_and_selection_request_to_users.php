<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Derniere connexion du createur a son espace (st_admin_date du legacy).
            $table->timestamp('last_login_at')->nullable()->index()->after('signup_referer');
            // Demande de selection faite depuis l'espace (us_formule_ask_date du legacy).
            $table->timestamp('selection_requested_at')->nullable()->index()->after('home_selection_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'selection_requested_at']);
        });
    }
};
