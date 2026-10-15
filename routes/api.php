<?php

use App\Http\Controllers\Api\AppareilController;
use App\Http\Controllers\Api\JetonController;
use App\Http\Controllers\Api\LectureController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\VisuelController;
use App\Http\Requests\Api\CompteRequest;
use App\Models\Domaine;
use App\Http\Resources\Api\CreatifResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 | API des applications (mobile, desktop). Version dans l'URL : une
 | application installee ne se met pas a jour en meme temps que le site.
 */
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('jetons', [JetonController::class, 'store'])
        ->middleware('throttle:api-jetons')->name('jetons.store');

    // Appele par Caddy (VPS) avant de demander un certificat pour un domaine :
    // 200 si le domaine est a nous et servi, 404 sinon. Sans corps.
    Route::get('domaines/autorise', fn (Illuminate\Http\Request $requete) => response()->noContent(
        Domaine::servis()->where('nom', Domaine::normaliser((string) $requete->query('domain')))->exists() ? 200 : 404
    ))->middleware('throttle:api-autorise')->name('domaines.autorise');

    // Connexion desktop par code (le mot de passe ne passe jamais par l'application).
    Route::post('appareils/code', [AppareilController::class, 'code'])
        ->middleware('throttle:api-jetons')->name('appareils.code');
    Route::post('appareils/jeton', [AppareilController::class, 'jeton'])
        ->middleware('throttle:api-codes')->name('appareils.jeton');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::delete('jetons/courant', [JetonController::class, 'destroy'])->name('jetons.destroy');

        Route::get('moi', fn (Request $requete) => new CreatifResource($requete->user()))
            ->middleware('ability:compte:lire')->name('moi');

        Route::patch('moi', function (CompteRequest $requete) {
            $requete->user()->update($requete->colonnes());

            return new CreatifResource($requete->user()->fresh());
        })->middleware('ability:compte:ecrire')->name('moi.update');

        Route::middleware('ability:images:lire')->group(function () {
            Route::get('portfolios', [PortfolioController::class, 'index'])->name('portfolios.index');
            Route::get('portfolios/{id}', [PortfolioController::class, 'show'])->whereNumber('id')->name('portfolios.show');
        });

        Route::middleware('ability:images:ecrire')->group(function () {
            Route::post('portfolios', [PortfolioController::class, 'store'])->name('portfolios.store');
            Route::put('portfolios/ordre', [PortfolioController::class, 'ordonner'])->name('portfolios.ordre');
            Route::patch('portfolios/{id}', [PortfolioController::class, 'update'])->whereNumber('id')->name('portfolios.update');
            Route::delete('portfolios/{id}', [PortfolioController::class, 'destroy'])->whereNumber('id')->name('portfolios.destroy');
            Route::put('portfolios/{id}/ordre', [PortfolioController::class, 'ordonnerVisuels'])->whereNumber('id')->name('portfolios.visuels.ordre');

            // Un fichier par requete : l'application envoie en parallele si besoin.
            Route::post('portfolios/{portfolio}/visuels', [VisuelController::class, 'store'])
                ->whereNumber('portfolio')->middleware('throttle:api-depots')->name('visuels.store');
            Route::patch('visuels/{id}', [VisuelController::class, 'update'])->whereNumber('id')->name('visuels.update');
            Route::post('visuels/{id}/image', [VisuelController::class, 'remplacer'])
                ->whereNumber('id')->middleware('throttle:api-depots')->name('visuels.remplacer');
            Route::delete('visuels/{id}', [VisuelController::class, 'destroy'])->whereNumber('id')->name('visuels.destroy');
        });

        Route::middleware('ability:messages:ecrire')->group(function () {
            Route::post('messages/{id}/reponses', [MessageController::class, 'repondre'])
                ->whereNumber('id')->middleware('throttle:api-depots')->name('messages.repondre');
            Route::delete('messages/poubelle', [MessageController::class, 'viderPoubelle'])->name('messages.poubelle.vider');
            Route::delete('messages/{id}', [MessageController::class, 'destroy'])->whereNumber('id')->name('messages.destroy');
            Route::post('messages/{id}/restaurer', [MessageController::class, 'restaurer'])->whereNumber('id')->name('messages.restaurer');
        });

        Route::get('pages', [LectureController::class, 'pages'])->middleware('ability:pages:lire')->name('pages.index');
        Route::middleware('ability:pages:ecrire')->group(function () {
            Route::post('rubriques', [PageController::class, 'storeRubrique'])->name('rubriques.store');
            Route::patch('rubriques/{id}', [PageController::class, 'updateRubrique'])->whereNumber('id')->name('rubriques.update');
            Route::delete('rubriques/{id}', [PageController::class, 'destroyRubrique'])->whereNumber('id')->name('rubriques.destroy');
            Route::put('rubriques/{id}/ordre', [PageController::class, 'ordonner'])->whereNumber('id')->name('rubriques.ordre');
            Route::post('rubriques/{rubrique}/pages', [PageController::class, 'storePage'])->whereNumber('rubrique')->name('pages.store');
            Route::patch('pages/{id}', [PageController::class, 'updatePage'])->whereNumber('id')->name('pages.update');
            Route::delete('pages/{id}', [PageController::class, 'destroyPage'])->whereNumber('id')->name('pages.destroy');
        });

        Route::middleware('ability:memo:ecrire')->group(function () {
            Route::post('memo', [LectureController::class, 'memoriser'])->name('memo.store');
            Route::delete('memo/{login}', [LectureController::class, 'oublier'])->name('memo.destroy');
        });

        Route::get('memo', [LectureController::class, 'memo'])->middleware('ability:memo:lire')->name('memo.index');
        Route::get('statistiques', [LectureController::class, 'statistiques'])->middleware('ability:stats:lire')->name('statistiques');

        Route::middleware('ability:messages:lire')->group(function () {
            Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
            Route::get('messages/{id}', [MessageController::class, 'show'])->whereNumber('id')->name('messages.show');
        });
    });
});
