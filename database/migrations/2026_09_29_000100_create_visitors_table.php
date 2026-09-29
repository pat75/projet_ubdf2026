<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Comptes visiteurs (App\Models\Visitor, guard `visitor`).
|
| Table a part de `users` : un visiteur n'a ni login, ni book, ni formule.
| Le melanger aux creatifs aurait oblige chaque requete de l'annuaire,
| des statistiques et du back-office a les exclure. Il se connecte par
| son adresse mail, unique ici (elle ne l'est pas chez les creatifs).
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('brand', 2)->default('ub');
            $table->string('locale', 5)->default('fr');
            $table->string('signup_ip', 45)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
