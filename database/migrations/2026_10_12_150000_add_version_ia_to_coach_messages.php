<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Version redigee par l'IA, figee a la generation : l'ecart avec l'envoye nourrit la memoire du coach. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coach_messages', function (Blueprint $table) {
            $table->string('objet_ia')->nullable()->after('corps');
            $table->text('corps_ia')->nullable()->after('objet_ia');
        });
    }

    public function down(): void
    {
        Schema::table('coach_messages', fn (Blueprint $table) => $table->dropColumn(['objet_ia', 'corps_ia']));
    }
};
