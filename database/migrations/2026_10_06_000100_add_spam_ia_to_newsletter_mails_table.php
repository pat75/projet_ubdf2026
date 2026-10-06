<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_mails', function (Blueprint $table) {
            // Null tant que Jev n'a pas evalue l'adresse.
            $table->boolean('spam_ia')->nullable();
            $table->float('spam_ia_probabilite')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_mails', function (Blueprint $table) {
            $table->dropColumn(['spam_ia', 'spam_ia_probabilite']);
        });
    }
};
