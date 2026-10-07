<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** Slug des mots-cles IA : adresse des pages /images/{slug}. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('slug', 90)->nullable()->after('label');
        });

        foreach (DB::table('tags')->get(['id', 'label']) as $tag) {
            DB::table('tags')->where('id', $tag->id)->update(['slug' => Str::slug($tag->label)]);
        }

        // Deux libelles peuvent donner le meme slug (« affiche » / « affiché ») :
        // index simple, la page regroupe alors les deux.
        Schema::table('tags', function (Blueprint $table) {
            $table->index(['slug', 'lang']);
        });
    }

    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex(['slug', 'lang']);
            $table->dropColumn('slug');
        });
    }
};
