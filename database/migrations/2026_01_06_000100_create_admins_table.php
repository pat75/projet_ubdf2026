<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Comptes d'administration, separes des comptes createurs.
 *
 * Le legacy protegeait admin_/ par un mot de passe partage en dur dans les
 * scripts. Ici chaque administrateur a son compte, et un createur ne peut
 * pas devenir administrateur en modifiant son propre profil.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
