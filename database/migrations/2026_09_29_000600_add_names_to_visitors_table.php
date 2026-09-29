<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Prenom et nom d'un compte visiteur, saisis sur sa page « Mon compte »
| (App\Livewire\Visiteur\Compte). Facultatifs : le compte s'ouvre avec la
| seule adresse mail, depuis le coeur du memoBook.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('firstname', 100)->nullable()->after('email');
            $table->string('lastname', 100)->nullable()->after('firstname');
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn(['firstname', 'lastname']);
        });
    }
};
