<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Memo book : les books mis de cote d'un clic sur le coeur (App\Models\MemoBook).
|
| Le proprietaire est soit un visiteur (`visitor_id`), soit un creatif
| connecte (`user_id`) ; `book_id` designe le creatif memorise. Deux cles
| etrangeres plutot qu'une relation polymorphe : la suppression d'un
| compte, d'un cote comme de l'autre, nettoie ses lignes en cascade.
|
| Remplace la selection du legacy, gardee dans le localStorage du
| navigateur (cle `books`) et perdue d'un appareil a l'autre.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memo_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['visitor_id', 'book_id']);
            $table->unique(['user_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memo_books');
    }
};
