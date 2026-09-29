<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Partage public d'un memoBook (App\Models\MemoPartage) : un lien
| /memobook/<jeton> qui montre la liste des books, sans messages
| ni retrait. Un par proprietaire (visiteur ou creatif). Desactive, le
| jeton est garde : reactiver redonne la meme adresse.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memo_partages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('jeton', 32)->unique();
            $table->boolean('actif')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memo_partages');
    }
};
