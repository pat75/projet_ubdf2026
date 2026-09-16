<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jetons de reinitialisation de mot de passe, rattaches a un **compte** et
 * non a une adresse.
 *
 * La table `password_reset_tokens` de Laravel a l'adresse pour cle primaire.
 * Elle ne convient pas ici : `users.email` n'est pas unique, parce que le
 * legacy laissait un meme creatif ouvrir plusieurs books avec la meme
 * adresse — au point d'avoir un message dedie (« Vous avez plus de deux
 * comptes, pensez a supprimer les comptes inutiles »). Une demande de
 * reinitialisation doit donc produire un lien par compte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_password_resets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Le jeton n'est jamais stocke en clair : seul son sha256 l'est.
            $table->string('token_hash', 64)->unique();

            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable();
            $table->string('request_ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_password_resets');
    }
};
