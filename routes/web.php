<?php

use Illuminate\Support\Facades\Route;

$bookDomain = config('ubdf.book_domain');

/*
|--------------------------------------------------------------------------
| Books creatifs — sous-domaine <login>.<book_domain>
|--------------------------------------------------------------------------
| Doit etre declare AVANT les routes du portail : Laravel evalue les routes
| dans l'ordre et une route sans contrainte de domaine capterait tout.
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
    Route::get('/', function () {
        return response('PORTAIL', 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    })->name('home');
});
