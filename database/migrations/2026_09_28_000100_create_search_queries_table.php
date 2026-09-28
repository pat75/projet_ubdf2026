<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Journal des recherches par mots-cles du portail (App\Models\SearchQuery),
| d'ou la page /search tire les mots-cles les plus recherches sur 90 jours.
| Les recherches par nom ne sont pas journalisees : ce sont des noms de
| personnes, sans interet pour ce palmares.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('q', 191);
            $table->string('brand', 8)->default('ub');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['brand', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_queries');
    }
};
