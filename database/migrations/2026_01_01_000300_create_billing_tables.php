<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ub2_fac et df2_fac fusionnees, la marque distinguant les deux.
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable();
            $table->string('legacy_source', 10)->nullable();   // ub2_fac | df2_fac
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('brand', 2)->default('ub')->index();
            $table->string('number')->unique();
            $table->string('label')->nullable();
            $table->string('designation', 400)->nullable();

            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('vat', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');

            $table->string('status', 20)->default('pending')->index();  // pending|paid|cancelled|refunded
            $table->string('gateway', 20)->nullable();                  // paypal|payplug|stripe
            $table->json('gateway_payload')->nullable();

            $table->timestamp('issued_at')->nullable()->index();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->unique(['legacy_source', 'legacy_id']);
            $table->index(['user_id', 'issued_at']);
        });

        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->string('code', 40)->unique();
            $table->string('label')->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('discount_type', 10)->default('amount');   // amount|percent
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->foreignId('sponsor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('referred_email')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('invoices');
    }
};
