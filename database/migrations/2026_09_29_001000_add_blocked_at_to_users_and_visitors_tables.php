<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blocage d'un compte, creatif ou visiteur.
 *
 * Distinct de la suppression douce (`deleted_at`) : un compte bloque
 * existe toujours, garde ses visuels et ses factures, reste visible au
 * back-office — il ne peut simplement plus se connecter. Le legacy n'avait
 * que l'effacement, qui ne laissait aucun retour en arriere propre.
 *
 * Le motif est note pour l'equipe : six mois plus tard, personne ne se
 * souvient pourquoi tel compte a ete ferme.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('admin_note')->index();
            $table->string('blocked_reason')->nullable()->after('blocked_at');
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('signup_ip')->index();
            $table->string('blocked_reason')->nullable()->after('blocked_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['blocked_at', 'blocked_reason']);
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn(['blocked_at', 'blocked_reason']);
        });
    }
};
