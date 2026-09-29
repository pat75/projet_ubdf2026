<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Actualites de l'espace : un bloc redige au back-office, montre sur le
 | tableau de bord du creatif et/ou du visiteur. Reprend le principe de
 | `dp_info_crea` d'Ultra-book diffusion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actualites', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('sous_titre')->nullable();
            $table->text('contenu');
            // Chemins des visuels, relatifs a `public` ou absolus.
            $table->json('images')->nullable();
            // creatif | visiteur | deux | tous
            $table->string('emplacement', 20)->default('deux');
            $table->integer('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index(['actif', 'emplacement', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actualites');
    }
};
