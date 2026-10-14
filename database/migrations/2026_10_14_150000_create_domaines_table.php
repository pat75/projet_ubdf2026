<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Noms de domaine personnels des creatifs, servis par le VPS (Caddy) puis
 | la passerelle (App\Http\Middleware\ServirDomainePerso). Voir
 | _doc/_doc_VPS/01_synthese_vps.md.
 |
 | users.custom_domain (reprise legacy) n'est pas reutilise : il ne contient
 | que les domaines des marques elles-memes, aucune donnee exploitable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domaines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nom')->unique();
            // en_attente, dns_ok, actif, suspendu, expire : voir App\Models\Domaine.
            $table->string('statut', 20)->default('en_attente')->index();
            // ovh : achete par la plateforme ; externe : deja possede par le creatif.
            $table->string('source', 10)->default('externe');
            $table->string('ovh_commande_id')->nullable();
            $table->date('expire_le')->nullable();
            $table->timestamp('verifie_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domaines');
    }
};
