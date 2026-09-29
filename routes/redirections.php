<?php

use App\Http\Controllers\Front\RedirectionLegacyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Anciennes URL du legacy
|--------------------------------------------------------------------------
|
| Elles sont indexees depuis des annees et figurent dans des e-mails, des
| signatures et des sites tiers. Celles qui ont un equivalent sont
| redirigees en 301 ; celles qui n'en ont plus renvoient vers la page la
| plus proche, jamais vers une 404.
|
| `ubdf:verifier-urls` confronte ce fichier au .htaccess de 2019.
*/

// Espace creatif : anciens raccourcis.
Route::redirect('/formules', '/espace/formule', 301);
Route::redirect('/formules_ultra-book', '/espace/formule', 301);
Route::redirect('/formules_dustfolio', '/espace/formule', 301);
Route::redirect('/messages', '/espace/messages', 301);
Route::redirect('/create', '/inscription', 301);
Route::redirect('/login', '/connexion', 301);

/*
 | Factures : l'identifiant du legacy est celui de `invoices.legacy_id`.
 | La redirection retrouve la facture correspondante ; sans elle, on
 | renvoie le createur vers sa liste de factures.
 */
Route::get('/facture_ultra-book_n__{id}', [RedirectionLegacyController::class, 'facture'])
    ->whereNumber('id')->name('legacy.facture.ub');
Route::get('/facture_n__{id}', [RedirectionLegacyController::class, 'facture'])
    ->whereNumber('id')->name('legacy.facture');
Route::get('/invoice_n__{id}', [RedirectionLegacyController::class, 'facture'])
    ->whereNumber('id')->name('legacy.invoice');

// Ancien point d'entree PayPal : le paiement passe par Payplug depuis 2017.
Route::get('/paypal_send_ultra-book_n__{reste}', fn () => redirect('/espace/formule', 301))
    ->where('reste', '.*');

/*
 | Fils de discussion : les liens `intermediate_msg_` du legacy portaient un
 | jeton dont le segment de controle ne verifiait rien (voir la migration
 | 2026_01_02_000200). Ils ne sont plus honores : le createur retrouve ses
 | demandes dans son espace, l'emetteur redemande un lien depuis le book.
 */
Route::get('/intermediate_msg_/{reste}', [RedirectionLegacyController::class, 'filPerime'])
    ->where('reste', '.*')->name('legacy.fil');

// Vieux points d'entree AJAX des scripts de 2010-2016, sans equivalent.
Route::get('/contact_show__{reste}', fn () => redirect('/accueil', 301))->where('reste', '.*');
Route::get('/contact_reponse{reste}', fn () => redirect('/accueil', 301))->where('reste', '.*');
Route::get('/intermediate_get', fn () => redirect('/espace/messages', 301));
Route::get('/intermediate_send_frombook', fn () => redirect('/accueil', 301));
Route::get('/formuleexits', fn () => redirect('/espace/formule', 301));
Route::get('/ubajax__{reste}', fn () => redirect('/accueil', 301))->where('reste', '.*');
Route::get('/fm_ajax', fn () => redirect('/espace/formule', 301));

/*
 | Support : le legacy renvoyait vers les sites vitrines
 | (ultra-book.pro, dustfolio.fr). Ces liens restent valables.
 */
Route::get('/support', [RedirectionLegacyController::class, 'support']);
Route::get('/contact', [RedirectionLegacyController::class, 'support']);

// Japonais : la langue n'est plus servie.
Route::redirect('/ja', '/accueil', 301);

// Pages du portail disparues.
Route::redirect('/memo', '/memobook', 301);
Route::redirect('/newsletters', '/actus', 301);
Route::redirect('/microbook_externe', '/accueil', 301);

// `book_<login>` ouvrait l'accueil sur l'ancre du book ; `-<login>` et
// `minibook_<login>` menaient a sa vignette. Tous mènent desormais au book.
Route::get('/book_{login}', [RedirectionLegacyController::class, 'book'])
    ->where('login', '[-a-zA-Z0-9_]+');
Route::get('/minibook_{login}', [RedirectionLegacyController::class, 'book'])
    ->where('login', '[-a-zA-Z0-9_]+');
Route::get('/-{login}', [RedirectionLegacyController::class, 'book'])
    ->where('login', '[-a-zA-Z0-9_]+');

// Articles du magazine appeles en AJAX par l'ancien JavaScript.
Route::get('/{slug}__wpactu_{id}', [RedirectionLegacyController::class, 'actualite'])
    ->where(['slug' => '[-a-zA-Z0-9]*', 'id' => '[0-9]{1,10}']);

// `page__<slug>`, `ultra-book__<slug>` et `dustfolio__<slug>` sont servies
// telles quelles par le portail (voir routes/web.php) : rien a rediriger.

/*
 | Actions du legacy (`ubaction__`, `ubactiontype__`) : celles qui servent
 | encore ont leur route propre, declaree avant ce fichier. Ce qui arrive
 | ici est une action disparue — offres de projets, anciens ecrans — et
 | repart vers l'accueil plutot que sur une 404.
 */
Route::get('/ubaction__{action}', fn () => redirect('/accueil', 301))->where('action', '.*');
Route::get('/ubactiontype__{type}', fn () => redirect('/accueil', 301))->where('type', '.*');
