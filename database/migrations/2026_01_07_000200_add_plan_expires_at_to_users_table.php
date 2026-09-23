<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Echeance de la formule, calculee une fois pour toutes.
 *
 * Elle se deduisait de plan_started_at + plan_months, ce qui obligeait a
 * ecrire DATE_ADD dans chaque filtre — du SQL propre a MySQL, et un index
 * inutilisable. La colonne est tenue a jour par User::prolongerFormule().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('plan_expires_at')->nullable()->after('plan_months')->index();
        });

        $aRemplir = DB::table('users')
            ->where('plan', '>', 0)
            ->whereNotNull('plan_started_at')
            ->where('plan_months', '>', 0);

        // Une seule requete sous MySQL ; ailleurs (sqlite des tests), au fil
        // des lignes, faute de DATE_ADD.
        if (DB::connection()->getDriverName() === 'mysql') {
            $aRemplir->update(['plan_expires_at' => DB::raw('DATE_ADD(plan_started_at, INTERVAL plan_months MONTH)')]);

            return;
        }

        $aRemplir->select('id', 'plan_started_at', 'plan_months')->orderBy('id')
            ->chunk(500, function ($lignes) {
                foreach ($lignes as $ligne) {
                    DB::table('users')->where('id', $ligne->id)->update([
                        'plan_expires_at' => Carbon::parse($ligne->plan_started_at)->addMonths((int) $ligne->plan_months),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('plan_expires_at');
        });
    }
};
