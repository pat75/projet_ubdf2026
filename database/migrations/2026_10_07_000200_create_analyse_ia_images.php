<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Analyse des visuels par IA : mots-cles, titre et description par image,
 * pour le moteur de recherche. Le creatif doit l'autoriser (opt-in).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_settings', function (Blueprint $table) {
            $table->boolean('allow_ai_analysis')->default(false)->after('diffuse_availability');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->string('ai_title')->nullable()->after('description');
            $table->string('ai_description')->nullable()->after('ai_title');
            $table->string('ai_status', 12)->nullable()->after('ai_description');  // ok|erreur
            $table->string('ai_model', 80)->nullable()->after('ai_status');
            $table->timestamp('analysed_at')->nullable()->index()->after('ai_model');

            // FULLTEXT : MySQL seulement (les tests tournent sous SQLite).
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->fullText(['ai_title', 'ai_description']);
            }
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('label', 80);
            $table->string('lang', 2);
            $table->timestamp('created_at')->useCurrent()->index();

            $table->unique(['label', 'lang']);
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->fullText('label');
            }
        });

        Schema::create('media_tag', function (Blueprint $table) {
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->primary(['media_id', 'tag_id']);
        });

        Schema::table('search_queries', function (Blueprint $table) {
            $table->unsignedInteger('nb_books')->nullable()->after('brand');
            $table->unsignedInteger('nb_images')->nullable()->after('nb_books');
            // Creatifs renvoyes en resultat (ids users), books et images confondus.
            $table->json('result_user_ids')->nullable()->after('nb_images');
        });
    }

    public function down(): void
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropColumn(['nb_books', 'nb_images', 'result_user_ids']);
        });

        Schema::dropIfExists('media_tag');
        Schema::dropIfExists('tags');

        Schema::table('media', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->dropFullText(['ai_title', 'ai_description']);
            }
            $table->dropIndex(['analysed_at']);
            $table->dropColumn(['ai_title', 'ai_description', 'ai_status', 'ai_model', 'analysed_at']);
        });

        Schema::table('book_settings', function (Blueprint $table) {
            $table->dropColumn('allow_ai_analysis');
        });
    }
};
