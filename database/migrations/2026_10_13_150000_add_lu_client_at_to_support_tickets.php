<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Reponse du support lue par le createur ou le visiteur : tant qu'elle ne
 | l'est pas, l'espace affiche le menu « Message support ».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->timestamp('lu_client_at')->nullable()->after('lu_admin_at');
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('lu_client_at');
        });
    }
};
