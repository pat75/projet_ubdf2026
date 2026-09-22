<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Offres promotionnelles presentees a un createur (inc_marketing du legacy).
 * Sert au rythme de la « promo auto 6 mois » : une offre valable 24 h,
 * proposee 2 mois apres l'inscription puis tous les 3 mois.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->timestamp('offered_at');
            $table->timestamps();

            $table->index(['user_id', 'type', 'offered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_offers');
    }
};
