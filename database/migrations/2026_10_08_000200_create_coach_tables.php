<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Coach crea : journal des operations des createurs, messages de coaching, interrupteur. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creatif_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 20);
            $table->string('subject_type');
            $table->string('subject_id', 40)->nullable();
            $table->string('subject_label')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('coach_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('session_fin');
            $table->json('diagnostic');
            $table->string('objet');
            $table->text('corps');
            $table->string('modele')->nullable();
            $table->string('statut', 10)->default('brouillon');
            $table->string('traite_par')->nullable();
            $table->timestamp('envoye_le')->nullable();
            $table->timestamps();
            $table->index(['statut', 'created_at']);
        });

        Schema::table('book_settings', function (Blueprint $table) {
            $table->boolean('coaching')->default(true)->after('allow_ai_analysis');
        });
    }

    public function down(): void
    {
        Schema::table('book_settings', fn (Blueprint $table) => $table->dropColumn('coaching'));
        Schema::dropIfExists('coach_messages');
        Schema::dropIfExists('creatif_activities');
    }
};
