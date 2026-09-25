<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            // Lien YouTube ou Vimeo : le visuel est alors la vignette de la
            // video (`filename`), que le book affiche comme une image et
            // remplace par le lecteur au clic.
            $table->string('video_url')->nullable()->after('link');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
