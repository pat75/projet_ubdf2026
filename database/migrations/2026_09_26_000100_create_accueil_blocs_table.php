<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Blocs d'accroche de la page d'accueil (partials/accueil-hero.blade.php)
| que l'administrateur peut afficher ou masquer, un par un, depuis le
| back-office (App\Filament\Pages\AccueilPage). Une ligne absente vaut
| « affiche » (App\Models\AccueilBloc::actif()).
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accueil_blocs', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accueil_blocs');
    }
};
