<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Selections editoriales (inc_auto_selection + inc_auto_selection_user).
        Schema::create('selections', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->string('brand', 2)->default('ub')->index();
            $table->string('category_slug', 60)->nullable()->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('selection_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('selection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('sent_twitter')->default(false);
            $table->boolean('sent_instagram')->default(false);
            $table->boolean('sent_mail')->default(false);
            $table->timestamps();

            $table->unique(['selection_id', 'user_id']);
        });

        // Statistiques de visite (inc_stats), agregees par jour.
        Schema::create('visit_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('public_views')->default(0);
            $table->unsignedInteger('admin_views')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });

        // Mots-cles de recherche du portail (ub2_stats_mcles).
        Schema::create('search_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term', 190)->index();
            $table->string('brand', 2)->default('ub');
            $table->unsignedInteger('hits')->default(1);
            $table->timestamps();

            $table->unique(['term', 'brand']);
        });

        // Emailing : newsletters, relances, marketing (nl_newsletter,
        // df2_mail_relance, inc_marketing).
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->string('brand', 2)->default('ub')->index();
            $table->string('type', 30)->default('newsletter')->index();  // newsletter|relance|marketing
            $table->string('name');
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->index();
            $table->string('status', 20)->default('queued')->index();   // queued|sent|opened|bounced|unsubscribed
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_sends');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('search_terms');
        Schema::dropIfExists('visit_stats');
        Schema::dropIfExists('selection_user');
        Schema::dropIfExists('selections');
    }
};
