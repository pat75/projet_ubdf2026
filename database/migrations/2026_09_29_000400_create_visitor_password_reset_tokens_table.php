<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Jetons de reinitialisation des comptes visiteurs (broker `visitors`,
| config/auth.php). Table a part de `password_reset_tokens` : les deux
| brokers indexent par adresse mail, et une meme adresse peut designer
| un creatif et un visiteur.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_password_reset_tokens');
    }
};
