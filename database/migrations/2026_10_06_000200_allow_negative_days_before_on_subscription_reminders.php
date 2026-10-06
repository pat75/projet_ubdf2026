<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // -1 : relance manuelle d'un abonnement deja echu.
    public function up(): void
    {
        Schema::table('subscription_reminders', function (Blueprint $table) {
            $table->tinyInteger('days_before')->change();
        });
    }

    public function down(): void
    {
        Schema::table('subscription_reminders', function (Blueprint $table) {
            $table->unsignedTinyInteger('days_before')->change();
        });
    }
};
