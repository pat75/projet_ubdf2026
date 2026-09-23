<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Journal des actions d'administration. Le legacy n'en tenait aucun : on
 * ne pouvait pas savoir qui avait change une formule ou supprime un book.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained()->nullOnDelete();
            $table->string('admin_name');
            $table->string('action', 20);            // created|updated|deleted|restored
            $table->string('subject_type');
            $table->string('subject_id', 100);
            $table->string('subject_label')->nullable();
            $table->json('changes')->nullable();     // champs modifies, avant/apres
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activities');
    }
};
