<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Exports « Mes donnees » demandes depuis /espace/exporter
| (App\Models\DataExport) : une archive ZIP par demande, preparee en file
| d'attente (App\Jobs\GenererExportCompte) puis gardee quelques jours.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 16)->default('en_attente');
            $table->string('fichier')->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->text('erreur')->nullable();
            $table->timestamp('termine_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_exports');
    }
};
