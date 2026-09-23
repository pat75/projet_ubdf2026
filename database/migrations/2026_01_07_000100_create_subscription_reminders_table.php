<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Relances d'abonnement deja envoyees.
 *
 * Le legacy n'en gardait pas trace : relancer deux fois le meme jour
 * envoyait deux fois le message. La cle unique l'interdit ici.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('expires_on');                   // echeance visee
            $table->unsignedTinyInteger('days_before');   // 5 ou 0
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['user_id', 'expires_on', 'days_before']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_reminders');
    }
};
