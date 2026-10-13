<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 | Demande deposee sans session avec l'adresse d'un compte existant :
 | l'identite n'est pas prouvee, la discussion ne se lit que connecte a ce
 | compte. Les demandes deja recues dans ce cas sont marquees.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->boolean('compte_requis')->default(false)->after('email');
        });

        DB::table('support_tickets')->whereNull('user_id')->whereNotNull('email')->orderBy('id')
            ->each(function ($ticket) {
                $createurs = DB::table('users')->where('email', $ticket->email)->whereNull('deleted_at')->pluck('id');
                $visiteur = DB::table('visitors')->where('email', $ticket->email)->exists();

                if ($createurs->isNotEmpty() || $visiteur) {
                    DB::table('support_tickets')->where('id', $ticket->id)->update([
                        'compte_requis' => true,
                        'user_id' => $createurs->count() === 1 ? $createurs->first() : null,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('compte_requis');
        });
    }
};
