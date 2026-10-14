<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Achat d'un nom de domaine depuis Ma formule : prix, titulaire fige a la
 | commande, preuves de consentement, paiement Payplug, facture, dates.
 | Voir _doc/_doc_VPS/03_achat_domaine.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domaines', function (Blueprint $table) {
            $table->string('extension', 10)->nullable()->after('nom');
            $table->decimal('prix_ttc', 8, 2)->nullable()->after('extension');
            // Donnees personnelles du titulaire, chiffrees (cast encrypted:array).
            $table->text('titulaire')->nullable()->after('source');
            $table->timestamp('cgu_acceptees_le')->nullable();
            $table->timestamp('renonciation_le')->nullable();
            $table->string('consentement_ip', 45)->nullable();
            $table->string('consentement_version', 20)->nullable();
            $table->string('payplug_id')->nullable()->index();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('reserve_le')->nullable();
            $table->timestamp('resilie_le')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('domaines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropColumn([
                'extension', 'prix_ttc', 'titulaire', 'cgu_acceptees_le', 'renonciation_le',
                'consentement_ip', 'consentement_version', 'payplug_id', 'reserve_le', 'resilie_le',
            ]);
        });
    }
};
