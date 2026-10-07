<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vues et « coeurs » des mini-books, repris du serveur de statistiques du
 * legacy (extra-book.com/2012_stats) : ils n'etaient pas dans la base.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('legacy_views')->default(0)->after('media_count');
            $table->unsignedInteger('legacy_likes')->default(0)->after('legacy_views');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['legacy_views', 'legacy_likes']);
        });
    }
};
