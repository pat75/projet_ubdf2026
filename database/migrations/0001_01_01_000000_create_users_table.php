<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('name');
            $table->string('name_plural')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();

            // Le login determine le sous-domaine du book : <login>.<book_domain>.
            $table->string('login', 50)->unique();
            $table->string('email')->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('brand', 2)->default('ub')->index();   // ub | df
            $table->string('locale', 5)->default('fr');

            // Identite
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('company')->nullable();
            $table->unsignedTinyInteger('civility')->nullable();
            $table->string('status')->nullable();                 // us_statut

            // Coordonnees
            $table->string('address')->nullable();
            $table->string('zipcode', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('mobile', 40)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Reseaux et site
            $table->string('website')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('custom_domain', 100)->nullable()->index();

            // Etat du compte
            $table->boolean('is_published')->default(false)->index();  // us_affhome
            $table->boolean('in_directory')->default(false)->index();  // us_anu
            $table->boolean('is_selected')->default(false)->index();   // us_ultraselection
            $table->boolean('is_available')->default(false)->index();  // us_dispo
            $table->boolean('accepts_sms')->default(false);
            $table->boolean('shares_link')->default(false);

            // Abonnement
            $table->unsignedTinyInteger('plan')->default(0)->index();  // us_formule
            $table->timestamp('plan_started_at')->nullable();
            $table->unsignedSmallInteger('plan_months')->nullable();

            // Quotas
            $table->unsignedBigInteger('storage_used')->default(0);
            $table->unsignedInteger('media_count')->default(0);

            // Traces d'inscription
            $table->string('signup_ip', 45)->nullable();
            $table->string('signup_referer')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('categories');
    }
};
