<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 | Marque d'origine de la demande (Ultra-book ou Dustfolio) : logo, objet
 | et liens des mails du support. Demandes existantes : marque du compte,
 | sinon Ultra-book.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('brand', 10)->default('ub')->after('user_id');
        });

        DB::table('support_tickets')->whereNotNull('user_id')->orderBy('id')->each(function ($ticket) {
            $brand = DB::table('users')->where('id', $ticket->user_id)->value('brand');

            if ($brand) {
                DB::table('support_tickets')->where('id', $ticket->id)->update(['brand' => $brand]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
