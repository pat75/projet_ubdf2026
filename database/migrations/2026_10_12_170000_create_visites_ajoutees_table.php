<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visites_ajoutees', function (Blueprint $table) {
            $table->id();
            $table->ulid('lot')->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('prevue_at');
            $table->unsignedTinyInteger('nb');
            $table->dateTime('appliquee_at')->nullable();
            $table->timestamps();

            $table->index(['appliquee_at', 'prevue_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visites_ajoutees');
    }
};
