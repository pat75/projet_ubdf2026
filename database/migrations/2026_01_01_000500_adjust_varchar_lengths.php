<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajustements issus de l'audit des varchar du legacy (_doc/08_memo_varchar.md).
 * Trois colonnes cibles reconduisaient une limite trop courte de ub2020.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_settings', function (Blueprint $table) {
            // us_pf_css etait un varchar(400) sature par 506 books : la
            // feuille de style d'un book n'a aucune raison d'etre bornee.
            $table->text('custom_css')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            // us_cp varchar(10) sature par 60 comptes : les codes postaux
            // etrangers depassent 10 caracteres.
            $table->string('zipcode', 20)->nullable()->change();

            // us_referer varchar(120) tronquait 1 249 URL d'inscription.
            $table->text('signup_referer')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('book_settings', function (Blueprint $table) {
            $table->string('custom_css', 400)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('zipcode', 10)->nullable()->change();
            $table->string('signup_referer')->nullable()->change();
        });
    }
};
