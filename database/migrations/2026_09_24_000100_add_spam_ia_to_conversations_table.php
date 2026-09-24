<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Distinct de `is_spam` (regles, listes, quotas — masque la
            // demande) : ce champ vient d'une detection IA sur le premier
            // message (config('messagerie.spam_filter')) et se contente
            // d'afficher un label. Null tant que l'evaluation n'a pas eu
            // lieu (fonctionnalite desactivee, appel en echec...).
            $table->boolean('spam_ia')->nullable()->after('is_spam');
            $table->float('spam_ia_probabilite')->nullable()->after('spam_ia');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['spam_ia', 'spam_ia_probabilite']);
        });
    }
};
