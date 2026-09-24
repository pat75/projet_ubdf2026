<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Images deposees dans le corps des pages (editeur Redactor de
 * App\Livewire\Espace\Pages) : stockees dans img_cms/ du book
 * (App\Support\DossierBook, servies par BookMediaController::cms), hors
 * du quota et des tables de portfolio (galleries/media).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('filename');
            $table->string('original_name')->nullable();

            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_images');
    }
};
