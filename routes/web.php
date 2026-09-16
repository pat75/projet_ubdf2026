<?php

use App\Http\Controllers\Front\AccueilController;
use App\Http\Controllers\Front\AnnuaireController;
use App\Http\Controllers\Front\BookMediaController;
use App\Http\Controllers\Front\CmsController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\FilController;
use App\Http\Controllers\Front\PortfolioController;
use App\Http\Controllers\Front\RechercheController;
use App\Http\Controllers\Front\StatsController;
use Illuminate\Support\Facades\Route;

$bookDomain = config('ubdf.book_domain');

/*
| Etiquettes de sous-domaine qui ne peuvent pas designer un book : elles
| servent au portail (« df » pour Dustfolio) ou a l'infrastructure. Sans
| cette exclusion, `df.<book_domain>` serait pris pour le book d'un creatif
| nomme « df » — et rien n'empechait un compte de reserver « www ».
*/
/*
| Le « $ » d'une alternative comme `(?!df$|www$)` s'ancre a la fin du sujet
| entier — ici l'hote complet, `df.ubdf2026.ultra-book.name` — et non a la
| fin de l'etiquette capturee. La negation ne mordait donc jamais. Le
| controle porte sur la limite d'etiquette : le mot reserve ne doit pas
| etre suivi d'un caractere d'etiquette.
*/
$reserves = implode('|', config('marques.sous_domaines_reserves'));
$loginPattern = '(?!(?:'.$reserves.')(?![-a-zA-Z0-9]))[-a-zA-Z0-9]+';

/*
|--------------------------------------------------------------------------
| Books creatifs — sous-domaine <login>.<book_domain>
|--------------------------------------------------------------------------
| Declare avant le portail : celui-ci repond sur tous les hotes et capterait
| aussi les sous-domaines.
*/
Route::domain('{login}.'.$bookDomain)
    ->where(['login' => $loginPattern])
    ->group(function () {
        Route::get('/', function (string $login) {
            return response("BOOK · login = {$login}", 200)
                ->header('Content-Type', 'text/plain; charset=utf-8');
        })->name('book.home');
    });

/*
|--------------------------------------------------------------------------
| Portail public — Ultra-book et Dustfolio
|--------------------------------------------------------------------------
| Aucune contrainte de domaine : le portail repond sur tous les hotes
| declares dans `config/marques.php`, et le middleware `ResoudreMarque`
| deduit la marque de l'hote. C'est le modele du legacy — un seul point
| d'entree, la marque venant de `HTTP_HOST` — sans sa table de motifs.
*/
Route::group([], function () {

    // Compteurs globaux, attendus par js_core_pages.js a ce chemin exact.
    Route::get('/cache_js/data_stats.json', StatsController::class)->name('stats');

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

    /*
     | Recherche
     |
     | `/rechercher_submit` est l'URL construite par js_core_pages.js : elle
     | garde le contrat du legacy (parametres a plat, tableau JSON).
     | `/recherche` est la page equivalente rendue par le serveur.
     */
    Route::get('/recherche', [RechercheController::class, 'page'])->name('recherche');
    Route::get('/recherche/cartes/{page}', [RechercheController::class, 'cartes'])
        ->where('page', '[0-9]{1,3}')->name('recherche.cartes');
    Route::get('/rechercher_submit', [RechercheController::class, 'legacy'])
        ->name('recherche.legacy');

    /*
     | Messagerie intermediee
     |
     | `/intermediate_send` et `/captcha_img` sont les URL construites par
     | js_core_cards.js ; elles gardent leur nom. Le fil de discussion, lui,
     | remplace les liens `/intermediate_msg_/cust<verif>/<token>/<selector>`
     | du legacy, dont le segment de controle etait derive du jeton et ne
     | verifiait donc rien (voir la migration 2026_01_02_000200).
     */
    Route::post('/intermediate_send', [ContactController::class, 'envoyer'])
        ->name('contact.envoyer');
    Route::get('/captcha_img', [ContactController::class, 'captcha'])->name('captcha');

    Route::get('/messages/{role}/{selector}/{jeton}', [FilController::class, 'show'])
        ->where(['role' => 'owner|sender', 'selector' => '[a-z0-9]{24}', 'jeton' => '[a-f0-9]{64}'])
        ->name('messagerie.fil');
    Route::post('/messages/{role}/{selector}/{jeton}', [FilController::class, 'repondre'])
        ->where(['role' => 'owner|sender', 'selector' => '[a-z0-9]{24}', 'jeton' => '[a-f0-9]{64}'])
        ->name('messagerie.repondre');

    /*
     | Pages editoriales et actualites
     |
     | Reprises de l'ancien WordPress du magazine, que le portail de 2019
     | chargeait dans son propre processus a chaque requete
     | (`require '../magazine/wp-load.php'`). Les URL sont conservees telles
     | quelles : elles sont indexees.
     */
    Route::get('/actus', [CmsController::class, 'actualites'])->name('actualites');
    Route::get('/actus/{slug}', [CmsController::class, 'actualite'])
        ->where('slug', '[-a-zA-Z0-9_]+')->name('actualite');

    Route::get('/doc/{slug}', [CmsController::class, 'page'])
        ->where('slug', '[-a-zA-Z0-9_]+')->name('cms.doc');
    Route::get('/page__{slug}', [CmsController::class, 'page'])
        ->where('slug', '[-a-zA-Z0-9_]+')->name('cms.page');

    // Anciennes formes des memes pages, par marque.
    foreach (['ultra-book', 'dustfolio'] as $marque) {
        Route::get('/'.$marque.'__{slug}', [CmsController::class, 'page'])
            ->where('slug', '[-a-zA-Z0-9_]+');
    }

    // Le blog du legacy renvoyait vers un site externe ; il rejoint les actus.
    Route::get('/blog', fn () => redirect()->route('actualites', status: 301));

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
