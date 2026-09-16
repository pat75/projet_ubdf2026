<?php

use App\Http\Controllers\Front\AccueilController;
use App\Http\Controllers\Front\AnnuaireController;
use App\Http\Controllers\Front\BookMediaController;
use App\Http\Controllers\Front\PortfolioController;
use Illuminate\Support\Facades\Route;

$bookDomain = config('ubdf.book_domain');

/*
|--------------------------------------------------------------------------
| Books creatifs — sous-domaine <login>.<book_domain>
|--------------------------------------------------------------------------
| Declare avant le portail : une route sans contrainte de domaine capterait
| aussi les sous-domaines.
*/
Route::domain('{login}.'.$bookDomain)->group(function () {
    Route::get('/', function (string $login) {
        return response("BOOK · login = {$login}", 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    })->name('book.home');
});

/*
|--------------------------------------------------------------------------
| Portail public
|--------------------------------------------------------------------------
*/
Route::domain($bookDomain)->group(function () {

    // Visuels des books : fichier reel, ou image par defaut s'il a disparu.
    Route::get('/books/{login}/{file}', [BookMediaController::class, 'show'])
        ->where(['login' => '[-a-zA-Z0-9]+', 'file' => '[^/]+'])
        ->name('book.media');

    Route::get('/', [AccueilController::class, 'index'])->name('home');
    Route::get('/accueil', [AccueilController::class, 'index'])->name('accueil');

    // Defilement infini : cartes rendues par le serveur.
    Route::get('/cartes/{categorie}/{page}', [AccueilController::class, 'cartes'])
        ->where(['categorie' => '[-a-z]+', 'page' => '[0-9]{1,3}'])
        ->name('cartes');

    // Ancien contrat JSON du legacy, conserve pour le JavaScript repris tel quel.
    Route::get('/accueil__{page}__{selection}__{type}', [AccueilController::class, 'ajax'])
        ->where(['page' => '[0-9]{1,3}', 'selection' => 'sel|ult|lub', 'type' => '[-a-z_]+'])
        ->name('accueil.ajax');

    // Selections editoriales.
    Route::get('/les-ultra-books', fn () => redirect()->route('accueil'))->name('selection.lub');
    Route::get('/les-ultra-selections', fn () => redirect()->route('accueil'))->name('selection.ult');

    // Annuaire alphabetique.
    Route::get('/annuaire', [AnnuaireController::class, 'index'])->name('annuaire');
    Route::get('/annuaire_{lettre}', [AnnuaireController::class, 'index'])
        ->where('lettre', '[a-z0-9]')->name('annuaire.lettre');

    // Fiche d'un book sur le portail (URL SEO historique).
    Route::get('/portfolio/{login}/{slug}', [PortfolioController::class, 'show'])
        ->where('login', '[-a-zA-Z0-9]+')->name('portfolio.show');

    // Categories metier.
    Route::get('/{categorie}', [AccueilController::class, 'categorie'])
        ->where('categorie', implode('|', array_column(config('categories.list'), 'slug')))
        ->name('categorie');

    // Landings SEO : meme contenu qu'une categorie, titre different.
    foreach (config('seo_routes.landings') as $url => $landing) {
        Route::get('/'.$url, [AccueilController::class, 'categorie'])
            ->defaults('categorie', $landing['categorie'])
            ->name('landing.'.$url);
    }

    /*
     | Redirections permanentes — les URL ci-dessous sont indexees mais ne
     | sont plus canoniques.
     */
    foreach (config('seo_routes.accueil_aliases') as $alias) {
        Route::get('/'.$alias, fn () => redirect()->route('accueil', status: 301));
    }

    foreach (config('seo_routes.category_aliases') as $alias => $slug) {
        Route::get('/'.$alias, fn () => redirect()->route('categorie', ['categorie' => $slug], 301));
    }
});
