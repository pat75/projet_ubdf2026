<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * search_terms (reprise de ub2_stats_mcles) n'est lue ni ecrite par aucun
 * code : la recherche du portail journalise dans search_queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('search_terms');
    }

    public function down(): void
    {
        Schema::create('search_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term', 190)->index();
            $table->string('brand', 2)->default('ub');
            $table->unsignedInteger('hits')->default(1);
            $table->timestamps();

            $table->unique(['term', 'brand']);
        });
    }
};
