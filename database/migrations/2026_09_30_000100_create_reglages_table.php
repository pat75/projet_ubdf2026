<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reglages globaux du site, poses depuis le back-office : une ligne par
 * cle (voir App\Models\Reglage). La premiere est la mise en maintenance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reglages', function (Blueprint $table) {
            $table->id();
            $table->string('cle', 64)->unique();
            $table->text('valeur')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglages');
    }
};
