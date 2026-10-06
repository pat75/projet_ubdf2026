<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 | Les relances d'un abonnement echu peuvent se repeter (au plus une fois
 | tous les 7 jours, regle tenue par App\Services\Paiement\Relances) : la
 | cle unique porte desormais aussi le jour d'envoi. Les relances
 | automatiques J-5 et jour J restent protegees du double envoi le meme jour.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_reminders', function (Blueprint $table) {
            $table->date('sent_on')->nullable()->after('sent_at');
        });

        DB::table('subscription_reminders')->update(['sent_on' => DB::raw('DATE(sent_at)')]);

        Schema::table('subscription_reminders', function (Blueprint $table) {
            // La cle etrangere s'appuie sur l'index unique (user_id en tete) :
            // un index simple la porte pendant l'echange.
            $table->index('user_id', 'subscription_reminders_user_id_index');
            $table->dropUnique(['user_id', 'expires_on', 'days_before']);
            $table->unique(['user_id', 'expires_on', 'days_before', 'sent_on'], 'subscription_reminders_envoi_unique');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_reminders', function (Blueprint $table) {
            $table->dropUnique('subscription_reminders_envoi_unique');
            $table->unique(['user_id', 'expires_on', 'days_before']);
            $table->dropIndex('subscription_reminders_user_id_index');
            $table->dropColumn('sent_on');
        });
    }
};
