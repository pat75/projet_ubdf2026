<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Piece jointe (image ou PDF) d'un message au support, deposee par un
 | createur connecte dans books/…/<login>/support/.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->string('piece')->nullable()->after('corps');
            $table->string('piece_nom')->nullable()->after('piece');
        });
    }

    public function down(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropColumn(['piece', 'piece_nom']);
        });
    }
};
