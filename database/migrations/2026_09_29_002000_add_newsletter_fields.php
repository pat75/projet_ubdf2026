<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Newsletter : cibles d'envoi (createurs, visiteurs), trace de l'essai, et
 * marque portee par les adresses inscrites depuis le portail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // ["creatifs","visiteurs"] : au moins une cochee pour envoyer.
            $table->json('cibles')->nullable()->after('type');
            // Dernier essai envoye : l'envoi reel le reclame.
            $table->timestamp('essai_at')->nullable()->after('scheduled_at');
            $table->json('stats')->nullable()->after('sent_at');
        });

        Schema::table('campaign_sends', function (Blueprint $table) {
            // D'ou vient le destinataire : creatif abonne ou adresse du portail.
            $table->string('source', 20)->default('creatifs')->after('email');
            // Une adresse ne recoit une campagne qu'une fois, quelle que soit
            // sa source : la contrainte le garantit meme en cas de relance.
            $table->unique(['campaign_id', 'email']);
        });

        Schema::table('newsletter_mails', function (Blueprint $table) {
            $table->string('brand', 2)->default('ub')->after('email')->index();
            // Trace du refus, pour le suivi des desinscriptions.
            $table->timestamp('desabonne_at')->nullable()->after('ip');
        });

        Schema::table('book_settings', function (Blueprint $table) {
            $table->timestamp('newsletter_desabonne_at')->nullable()->after('diffuse_newsletter');
        });
    }

    public function down(): void
    {
        Schema::table('book_settings', function (Blueprint $table) {
            $table->dropColumn('newsletter_desabonne_at');
        });

        Schema::table('newsletter_mails', function (Blueprint $table) {
            $table->dropIndex(['brand']);
            $table->dropColumn(['brand', 'desabonne_at']);
        });

        Schema::table('campaign_sends', function (Blueprint $table) {
            $table->dropUnique(['campaign_id', 'email']);
            $table->dropColumn('source');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['cibles', 'essai_at', 'stats']);
        });
    }
};
