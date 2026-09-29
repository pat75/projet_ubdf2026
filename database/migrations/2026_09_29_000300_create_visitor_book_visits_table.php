<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Dernieres visites de books d'un visiteur connecte, pour son tableau de
| bord. Une ligne par couple visiteur + book : une nouvelle visite ne fait
| que remettre `visited_at` a jour. Rien n'est enregistre pour un visiteur
| anonyme.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_book_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('visited_at');

            $table->unique(['visitor_id', 'book_id']);
            $table->index(['visitor_id', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_book_visits');
    }
};
