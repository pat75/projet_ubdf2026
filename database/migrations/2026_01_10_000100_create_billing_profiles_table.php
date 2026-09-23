<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Identite d'entreprise des createurs qui facturent en professionnel.
 *
 * La facturation electronique impose, a partir de 2026, que chaque partie
 * soit identifiee par son SIRET et que la facture porte sa raison
 * sociale, son adresse et son numero de TVA. Ces informations sont
 * relevees aupres de l'annuaire des entreprises de l'Etat plutot que
 * saisies : c'est la seule facon d'en garantir l'exactitude.
 *
 * Une ligne par createur, creee seulement s'il se declare professionnel.
 * `payload` conserve la reponse complete de l'API : le format des champs
 * evolue, et on veut pouvoir en ressortir une donnee sans redemander.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('siret', 14)->index();
            $table->string('siren', 9)->index();

            $table->string('company_name');
            $table->string('legal_form', 4)->nullable();      // nature juridique INSEE
            $table->string('naf_code', 8)->nullable();        // activite principale
            $table->string('vat_number', 20)->nullable();     // TVA intracommunautaire

            $table->string('address')->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('country', 2)->default('FR');

            // `A` actif, `C` cesse : un etablissement ferme reste
            // enregistrable, le createur ayant pu changer de structure.
            $table->string('admin_state', 1)->nullable();
            $table->date('established_on')->nullable();

            $table->json('payload')->nullable();
            $table->timestamp('checked_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_profiles');
    }
};
