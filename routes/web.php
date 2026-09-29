<?php

use App\Http\Controllers\Admin\PriseIdentiteController;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\Front\AccueilController;
use App\Http\Controllers\Front\AnnuaireController;
use App\Http\Controllers\Front\BookController;
use App\Http\Controllers\Front\BookMediaController;
use App\Http\Controllers\Front\CmsController;
use App\Http\Controllers\Front\ConnexionController;
use App\Http\Controllers\Memo\FilMemoController;
use App\Http\Controllers\Memo\MemoController;
use App\Http\Controllers\Memo\MemoPublicController;
use App\Http\Controllers\Memo\PdfMemoController;
use App\Http\Controllers\Visiteur\InscriptionVisiteurController;
use App\Http\Controllers\Visiteur\MotDePasseVisiteurController;
use App\Http\Controllers\Visiteur\TableauVisiteurController;
use App\Http\Controllers\Visiteur\VisiteBookController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\DesabonnementController;
use App\Http\Controllers\Front\EditionBookController;
use App\Http\Controllers\Front\EspaceController;
use App\Http\Controllers\Front\MicrobookController;
use App\Http\Controllers\Front\NewsletterController;
use App\Http\Controllers\Front\StatsBookController;
use App\Http\Controllers\Espace\ExportController;
use App\Http\Controllers\Espace\FormuleController;
use App\Http\Controllers\Espace\GalerieController;
use App\Http\Controllers\Espace\PageController;
use App\Http\Controllers\Espace\PageImageController;
use App\Http\Controllers\Espace\PaiementController;
use App\Http\Controllers\Espace\PdfController;
use App\Http\Controllers\Espace\StatistiquesController;
use App\Http\Controllers\Front\FilController;
use App\Http\Controllers\Front\GoogleController;
use App\Http\Controllers\Front\InscriptionController;
use App\Http\Controllers\Front\MotDePasseController;
use App\Http\Controllers\Front\PortfolioController;
use App\Http\Controllers\Front\RechercheController;
use App\Http\Controllers\Front\LlmsController;
use App\Http\Controllers\Front\RobotsController;
use App\Http\Controllers\Front\SitemapController;
use App\Http\Controllers\Front\StatsController;
use App\Http\Middleware\ForcerLangue;
use App\Http\Middleware\ResoudreLangue;
use App\Services\Images\Declinaison;
use Illuminate\Support\Facades\Route;


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
| Books creatifs — sous-domaine <login>.<domaine des books de la marque>
|--------------------------------------------------------------------------
| Declare avant le portail : celui-ci repond sur tous les hotes et capterait
| aussi les sous-domaines.
*/
/*
| Un seul groupe pour les books des deux marques : le domaine est un
| parametre, limite aux domaines de books declares (config/marques.php).
| BookSurSonDomaine renvoie un book demande sur le domaine de l'autre
| marque vers le sien.
*/
Route::domain('{login}.{domaineBooks}')
    ->where([
        'login' => $loginPattern,
        'domaineBooks' => implode('|', array_map(fn (string $d) => preg_quote($d, '/'), App\Support\Marque::domainesBooks())),
    ])
    ->middleware(App\Http\Middleware\BookSurSonDomaine::class)
    ->group(function () {
        /*
         | URL du legacy, conservees a l'identique : elles sont indexees.
         | `-p<id>` designe une galerie, `-r<id>-c<id>` une page d'une
         | rubrique ; le titre qui precede n'est pas verifie, comme dans le
         | legacy (un titre modifie ne casse pas le lien).
         */
        Route::get('/', [BookController::class, 'accueil'])->name('book.home');
        Route::get('/ubstats.gif', StatsBookController::class)->name('book.stats');
        Route::get('/accueil', [BookController::class, 'accueil'])->name('book.accueil');
        Route::get('/portfolio', [BookController::class, 'portfolio'])->name('book.portfolio');
        Route::get('/news', [BookController::class, 'actualites'])->name('book.news');
        Route::get('/actualites', [BookController::class, 'actualites']);
        Route::get('/contact', [BookController::class, 'contact'])->name('book.contact');
        Route::post('/contact', [BookController::class, 'envoyer'])->name('book.contact.envoyer');

        // Captcha du formulaire de contact : servi par le sous-domaine, dont
        // la session garde le code.
        Route::get('/captcha/{formulaire}', CaptchaController::class)->name('book.captcha');

        // Mode edition (Ultra-frais / Ultra-zen) : entree par lien signe
        // emis depuis l'espace, puis reglages enregistres un a un.
        Route::get('/edition/{jeton}', [EditionBookController::class, 'entrer'])
            ->middleware('signed')->name('book.edition.entrer');
        Route::post('/reglages', [EditionBookController::class, 'enregistrer'])
            ->middleware('auth:web')->name('book.reglages');

        Route::get('/{titre}-r{rub}-c{pag}', [BookController::class, 'page'])
            ->where(['titre' => '[-_0-9A-Za-z]*', 'rub' => '[0-9]{1,12}', 'pag' => '[0-9]{1,12}'])
            ->name('book.page');
        // Version iPhone : galerie.
        Route::get('/{titre}-pi{rub}', [BookController::class, 'galerieMobile'])
            ->where(['titre' => '[-_0-9A-Za-z]*', 'rub' => '[0-9]{1,12}'])
            ->name('book.galerie.mobile');
        Route::get('/{titre}-p{rub}', [BookController::class, 'galerie'])
            ->where(['titre' => '[-_0-9A-Za-z]*', 'rub' => '[0-9]{1,12}'])
            ->name('book.galerie');
        // Mot de passe d'un portfolio protege.
        Route::post('/{titre}-p{rub}', [BookController::class, 'deverrouiller'])
            ->where(['titre' => '[-_0-9A-Za-z]*', 'rub' => '[0-9]{1,12}'])
            ->name('book.galerie.deverrouiller');
        Route::post('/{titre}-pi{rub}', [BookController::class, 'deverrouiller'])
            ->where(['titre' => '[-_0-9A-Za-z]*', 'rub' => '[0-9]{1,12}'])
            ->name('book.galerie.mobile.deverrouiller');
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
/*
| Points d'entree techniques : JSON du defilement, visuels, captcha, depot
| du formulaire de contact.
|
| Ils ne portent **jamais** de segment de langue, sur aucune marque. Le
| JavaScript repris du front 2018 les appelle a des chemins ecrits en dur
| (`/rechercher_submit`, `/captcha_img`, `/accueil__…`) : les prefixer
| reviendrait a les rendre introuvables des que Dustfolio sert une page.
| Ils ne rendent d'ailleurs pas de texte a traduire.
*/
Route::group([], function () {

    /*
     | Sitemap et robots.txt : rendus, pas des fichiers statiques. Le legacy
     | maintenait a la main deux sitemaps XML ou ne figurait aucun book.
     */
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
    Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
    Route::get('/sitemap-books-{paquet}.xml', [SitemapController::class, 'books'])
        ->whereNumber('paquet')->name('sitemap.books');
    Route::redirect('/sitemap', '/sitemap.xml', 301);
    Route::get('/robots.txt', RobotsController::class)->name('robots');
    // Presentation du portail pour les assistants IA (llmstxt.org).
    Route::get('/llms.txt', LlmsController::class)->name('llms');

    // Compteurs globaux, attendus par js_core_pages.js a ce chemin exact.
    Route::get('/cache_js/data_stats.json', StatsController::class)->name('stats');

    /*
     | Visuels des books.
     |
     | Deux formes : avec declinaison nommee, ou sans — la declinaison par
     | defaut s'applique alors. Les dimensions ne figurent jamais dans
     | l'URL, seul le nom d'une declinaison declaree dans `config/images.php`.
     | C'etait la faiblesse de phpThumb, que ce point d'entree remplace : il
     | acceptait ses dimensions de l'appelant, donc n'importe qui pouvait
     | faire fabriquer n'importe quelle image.
     |
     | Un fichier absent rend l'image par defaut, comme le .htaccess de 2019.
     */
    Route::get('/books/{login}/cms/{chemin}', [BookMediaController::class, 'cms'])
        ->where(['login' => '[-a-zA-Z0-9]+', 'chemin' => '.+'])
        ->name('book.media.cms');

    Route::get('/books/{login}/{declinaison}/{file}', [BookMediaController::class, 'showDeclinaison'])
        ->where([
            'login' => '[-a-zA-Z0-9]+',
            'declinaison' => implode('|', Declinaison::noms()),
            'file' => '[^/]+',
        ])
        ->name('book.media.declinaison');

    Route::get('/books/{login}/{file}', [BookMediaController::class, 'show'])
        ->where(['login' => '[-a-zA-Z0-9]+', 'file' => '[^/]+'])
        ->name('book.media');

    // Defilement infini : cartes rendues par le serveur.
    Route::get('/cartes/{categorie}/{page}', [AccueilController::class, 'cartes'])
        ->where(['categorie' => '[-a-z]+', 'page' => '[0-9]{1,3}'])
        ->name('cartes');

    // Ancien contrat JSON du legacy, conserve pour le JavaScript repris tel quel.
    Route::get('/accueil__{page}__{selection}__{type}', [AccueilController::class, 'ajax'])
        ->where(['page' => '[0-9]{1,3}', 'selection' => 'sel|ult|lub', 'type' => '[-a-z_]+'])
        ->name('accueil.ajax');

    /*
     | Seconde forme de la meme URL, dans l'autre ordre
     | (`/accueil__sel__all__2`). Le .htaccess de 2019 declarait les deux ;
     | des pages en cache et des liens en portent encore.
     */
    Route::get('/accueil__{selection}__{type}__{page}', [AccueilController::class, 'ajaxInverse'])
        ->where(['selection' => 'sel|ult|lub', 'type' => '[-a-z_]+', 'page' => '[0-9]{1,3}'])
        ->name('accueil.ajax.inverse');

    /*
     | Recherche
     |
     | `/rechercher_submit` est l'URL construite par js_core_pages.js : elle
     | garde le contrat du legacy (parametres a plat, tableau JSON).
     | `/recherche` est la page equivalente rendue par le serveur.
     */
    Route::get('/recherche/cartes/{page}', [RechercheController::class, 'cartes'])
        ->where('page', '[0-9]{1,3}')->name('recherche.cartes');
    Route::get('/recherche/suggestions', [RechercheController::class, 'suggestions'])
        ->middleware('throttle:300,1')->name('recherche.suggestions');
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
    // Notification de paiement Payplug (serveur a serveur, sans session).
    Route::post('/payplug/notification', [PaiementController::class, 'notification'])
        ->name('payplug.notification');

    // Microbook : URL du legacy, collee telle quelle dans des sites tiers.
    Route::get('/microbook_{admin}_{pied}__{login}', MicrobookController::class)
        ->where(['admin' => '[0-9]', 'pied' => '[0-9]', 'login' => '[a-z0-9_-]+'])
        ->name('microbook');

    Route::post('/intermediate_send', [ContactController::class, 'envoyer'])
        ->name('contact.envoyer');
    // Captcha local (App\Services\Captcha\Captcha). /captcha_img garde
    // l'adresse du legacy pour la fenetre « Contacter ».
    Route::get('/captcha_img', CaptchaController::class)->name('captcha');
    Route::get('/captcha/{formulaire}', CaptchaController::class)->name('captcha.formulaire');

    // Inscription a la newsletter (pied de page, menu du portail).
    Route::post('/newsletter', NewsletterController::class)
        ->middleware('throttle:10,1')->name('newsletter.inscription');

    /*
     | Comptes creatifs
     |
     | `/ubaction__user_open` et `/ubaction__user_out` gardent les chemins
     | du legacy : le formulaire de la fenetre modale, repris tel quel du
     | front de 2019, y poste en dur. Ils restent hors langue, comme les
     | autres points d'entree du JavaScript.
     |
     | `/inscription` remplace en revanche `/front/ajax_2010.php`, et c'est
     | le seul chemin que ce lot deplace. Nginx attribue toute URL en
     | `.php` a PHP-FPM avant que Laravel ne la voie : le fichier n'existant
     | pas, le serveur repondait « File not found. » La route etait
     | pourtant declaree et les tests verts — le client de test de Laravel
     | ne passe pas par nginx. Les deux `url` de `js_core_inscription.js`
     | sont mises a jour en consequence.
     */
    Route::post('/ubaction__user_open', [ConnexionController::class, 'connecter'])
        ->name('connexion');
    Route::post('/ubaction__user_out', [ConnexionController::class, 'deconnecter'])
        ->name('deconnexion');

    /*
     | Memo book (store Alpine `memo`) : appels JSON, hors langue. Le
     | proprietaire est un visiteur ou un creatif connecte ; un anonyme
     | ouvre un compte visiteur par /memo/compte.
     */
    Route::middleware(['auth:web,visitor', 'throttle:60,1'])->prefix('memo')->name('memo.')->group(function () {
        Route::post('/ajouter', [MemoController::class, 'ajouter'])->name('ajouter');
        Route::post('/retirer', [MemoController::class, 'retirer'])->name('retirer');
        Route::post('/fusionner', [MemoController::class, 'fusionner'])->name('fusionner');
    });
    Route::post('/memo/compte', [InscriptionVisiteurController::class, 'creer'])
        ->middleware('throttle:10,1')->name('visiteur.inscription');

    // Pixel des dernieres visites d'un visiteur connecte (book/commun/_pixel).
    Route::get('/ubvisite/{login}.gif', VisiteBookController::class)
        ->where('login', '[-a-zA-Z0-9_]+')->name('visiteur.vu');

    // Disponibilite d'un identifiant, interrogee a la frappe (texte brut).
    Route::get('/inscription', [InscriptionController::class, 'loginDisponible'])
        ->name('inscription.login-disponible');

    // Inscription et mot de passe oublie, aiguilles sur `form_id`.
    Route::post('/inscription', [InscriptionController::class, 'soumettre'])
        ->name('inscription.soumettre');

    // Connexion et creation de book par Google (GoogleController).
    Route::get('/auth/google', [GoogleController::class, 'redirection'])->name('google.redirection');
    Route::get('/auth/google/callback', [GoogleController::class, 'retour'])->name('google.retour');
    Route::post('/auth/google/inscription', [GoogleController::class, 'inscrire'])
        ->middleware('throttle:10,1')->name('google.inscription');
    Route::post('/auth/google/abandon', [GoogleController::class, 'abandonner'])->name('google.abandon');

    Route::get('/messages/{role}/{selector}/{jeton}', [FilController::class, 'show'])
        ->where(['role' => 'owner|sender', 'selector' => '[a-z0-9]{24}', 'jeton' => '[a-f0-9]{64}'])
        ->name('messagerie.fil');
    Route::post('/messages/{role}/{selector}/{jeton}', [FilController::class, 'repondre'])
        ->where(['role' => 'owner|sender', 'selector' => '[a-z0-9]{24}', 'jeton' => '[a-f0-9]{64}'])
        ->name('messagerie.repondre');

});

/*
| Pages du portail.
|
| Ce sont elles qui portent la langue : chacune est enregistree une fois
| sans prefixe (Ultra-book) et une fois par langue servie (Dustfolio).
*/
$portail = function (?string $langue = null) {

    Route::get('/', [AccueilController::class, 'index'])->name('home');
    Route::get('/accueil', [AccueilController::class, 'index'])->name('accueil');

    Route::get('/recherche', [RechercheController::class, 'page'])->name('recherche');
    // Page ouverte par la loupe du menu : resultats charges en ajax sous le bloc.
    Route::get('/search', [RechercheController::class, 'search'])->name('search');

    // Page « Creer un book », segment traduit (config/slugs.php). Les
    // segments des autres langues renvoient vers celui-ci.
    $slugs = config('slugs.inscription');
    $slug = $slugs[$langue ?? 'fr'] ?? $slugs['fr'];
    Route::get('/'.$slug, [InscriptionController::class, 'page'])->name('inscription.page');
    foreach (array_unique(array_diff($slugs, [$slug])) as $autre) {
        Route::get('/'.$autre, fn () => redirect()->to(lien('inscription.page'), 301));
    }

    /*
     | Comptes creatifs — les pages, celles-ci traduites.
     |
     | `espace` (tableau de bord de l'espace creatif) est la destination de
     | la connexion, de l'inscription et de la reinitialisation.
     */
    Route::get('/espace', EspaceController::class)
        ->middleware('auth:web')->name('espace');

    Route::middleware('auth:web')->prefix('espace')->name('espace.')->group(function () {
        Route::get('/galeries', [GalerieController::class, 'index'])->name('galeries');
        Route::get('/galeries/{galerie}', [GalerieController::class, 'show'])
            ->can('update', 'galerie')->name('galeries.show');
        Route::get('/modifier-mon-book', [EditionBookController::class, 'lien'])->name('edition-book');
        Route::get('/pages', [PageController::class, 'index'])->name('pages');
        Route::get('/pages/images', [PageImageController::class, 'index'])->name('pages.images.index');
        Route::post('/pages/upload-image', [PageImageController::class, 'store'])->name('pages.upload-image');
        Route::post('/pages/images/{image}', [PageImageController::class, 'update'])->name('pages.images.update');
        Route::delete('/pages/images/{image}', [PageImageController::class, 'destroy'])->name('pages.images.destroy');
        Route::get('/pages/{page}', [PageController::class, 'edit'])->name('pages.edit');
        Route::view('/habillage', 'espace.habillage')->name('design');
        Route::view('/diffusion', 'espace.diffusion')->name('diffusion');
        Route::view('/compte', 'espace.compte')->name('compte');
        Route::view('/messages', 'espace.messages')->name('messages');
        Route::get('/statistiques', StatistiquesController::class)->name('statistiques');
        Route::get('/formule', [FormuleController::class, 'index'])->name('formule');
        Route::post('/formule/payer/{option}', [PaiementController::class, 'payer'])
            ->whereNumber('option')->middleware('throttle:10,1')->name('formule.payer');
        Route::get('/formule/retour', [PaiementController::class, 'retour'])->name('formule.retour');
        Route::view('/exporter', 'espace.exporter')->name('exporter');
        Route::get('/exporter/archive/{export}', [ExportController::class, 'telecharger'])
            ->whereNumber('export')->name('export.telecharger');
        Route::get('/exporter/pdf', PdfController::class)->middleware('throttle:10,1')->name('pdf');
        Route::get('/factures/{facture}', [FormuleController::class, 'facture'])->name('facture');
        Route::get('/factures/{facture}/pdf', [FormuleController::class, 'facturePdf'])->name('facture.pdf');
    });

    /*
     | Memo book : page commune au creatif (dans son espace) et au visiteur.
     | Compte visiteur : tableau de bord, fils de messages, confirmation
     | d'adresse et mot de passe.
     */
    Route::middleware('auth:web,visitor')->group(function () {
        Route::view('/memobook', 'memo.index')->name('memobook');
        Route::get('/memobook/pdf', PdfMemoController::class)->middleware('throttle:10,1')->name('memobook.pdf');
        Route::get('/memobook/messages/{conversation}', FilMemoController::class)->whereNumber('conversation')->name('memobook.message');
    });

    // Version publique d'un memoBook partage (lecture seule).
    // Jeton de 10 caracteres exactement : ne peut pas capter /memobook/pdf.
    Route::get('/memobook/{jeton}', [MemoPublicController::class, 'page'])
        ->where('jeton', '[A-Za-z0-9]{10}')->name('memobook.public');
    Route::get('/memobook/{jeton}/pdf', [MemoPublicController::class, 'pdf'])
        ->where('jeton', '[A-Za-z0-9]{10}')->middleware('throttle:10,1')->name('memobook.public.pdf');

    Route::middleware('auth:visitor')->prefix('visiteur')->name('visiteur.')->group(function () {
        Route::get('/', TableauVisiteurController::class)->name('tableau');
        Route::view('/compte', 'visiteur.compte')->name('compte');
        Route::view('/messages', 'visiteur.messages')->name('messages');
        Route::post('/confirmation', [InscriptionVisiteurController::class, 'renvoyer'])
            ->middleware('throttle:3,10')->name('confirmation');
    });

    Route::get('/visiteur/confirmer/{visitor}/{hash}', [InscriptionVisiteurController::class, 'confirmer'])
        ->middleware('signed')->whereNumber('visitor')->name('visiteur.confirmer');
    Route::get('/visiteur/mot-de-passe/{jeton}', [MotDePasseVisiteurController::class, 'formulaire'])
        ->name('visiteur.mot-de-passe');
    Route::post('/visiteur/mot-de-passe/{jeton}', [MotDePasseVisiteurController::class, 'enregistrer'])
        ->name('visiteur.mot-de-passe.enregistrer');

    /*
     | Les memes factures, pour le back-office : un administrateur n'est pas
     | le proprietaire, il lui faut donc sa propre porte d'entree.
     */
    Route::middleware('auth:admin')->prefix('admin/factures')->name('admin.facture')->group(function () {
        Route::get('/{facture}', [FormuleController::class, 'facture']);
        Route::get('/{facture}/pdf', [FormuleController::class, 'facturePdf'])->name('.pdf');
    });

    /*
     | Prise d'identite depuis le back-office : voir l'espace creatif tel
     | que le createur le voit. La reddition n'est pas derriere
     | `auth:admin` — la garde y est toujours ouverte, mais on veut
     | pouvoir rendre la main meme si la session admin a expire entre
     | temps ; le controleur verifie alors le temoin de session.
     */
    Route::get('/admin/prise-identite/{creatif}', [PriseIdentiteController::class, 'relais'])
        ->middleware('auth:admin')->name('admin.prise-identite.relais');
    Route::post('/admin/prise-identite/{creatif}', [PriseIdentiteController::class, 'prendre'])
        ->middleware('auth:admin')->name('admin.prise-identite');
    Route::post('/admin/prise-identite', [PriseIdentiteController::class, 'rendre'])
        ->name('admin.prise-identite.rendre');

    // Desabonnement newsletter : lien signe present dans chaque campagne.
    Route::get('/newsletter/desabonnement/{user}', DesabonnementController::class)
        ->middleware('signed')->name('newsletter.desabonnement');

    Route::get('/inscription/confirmer/{user}', [InscriptionController::class, 'confirmer'])
        ->middleware('signed')->name('inscription.confirmer');

    Route::get('/mot-de-passe/{demande}/{jeton}', [MotDePasseController::class, 'formulaire'])
        ->where(['demande' => '[0-9]+', 'jeton' => '[a-f0-9]{64}'])
        ->name('mot-de-passe.formulaire');
    Route::post('/mot-de-passe/{demande}/{jeton}', [MotDePasseController::class, 'enregistrer'])
        ->where(['demande' => '[0-9]+', 'jeton' => '[a-f0-9]{64}'])
        ->name('mot-de-passe.enregistrer');

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
    Route::get('/blog', fn () => redirect()->to(lien('actualites'), 301));

    // Selections editoriales.
    Route::get('/les-ultra-books', fn () => redirect()->to(lien('accueil')))->name('selection.lub');
    Route::get('/les-ultra-selections', fn () => redirect()->to(lien('accueil')))->name('selection.ult');

    // Annuaire alphabetique.
    Route::get('/annuaire', [AnnuaireController::class, 'index'])->name('annuaire');
    Route::get('/annuaire_{lettre}', [AnnuaireController::class, 'index'])
        ->where('lettre', '[a-z0-9]')->name('annuaire.lettre');

    // Fiche d'un book sur le portail (URL SEO historique).
    Route::get('/portfolio/{login}/{slug}', [PortfolioController::class, 'show'])
        ->where('login', '[-a-zA-Z0-9]+')->name('portfolio.show');

    /*
     | Categories metier. Le segment suit la langue (App\Support\Metier::slugUrl) :
     | /illustrateur, /en/illustrator. En anglais, l'ancien segment francais
     | redirige (301) vers le nouveau.
     */
    Route::get('/{categorie}', [AccueilController::class, 'categorie'])
        ->where('categorie', implode('|', App\Support\Metier::slugsUrl($langue)))
        ->name('categorie');

    if ($langue === 'en') {
        foreach (config('categories.list') as $metier) {
            if (($metier['slug_en'] ?? $metier['slug']) !== $metier['slug']) {
                Route::get('/'.$metier['slug'], fn () => redirect()->to(lien_metier($metier['slug']), 301));
            }
        }
    }

    // Landings SEO : meme contenu qu'une categorie, titre different. Leurs
    // adresses et leurs textes sont francais : absentes de la version anglaise.
    if ($langue === null || $langue === 'fr') {
        foreach (config('seo_routes.landings') as $url => $landing) {
            Route::get('/'.$url, [AccueilController::class, 'categorie'])
                ->defaults('categorie', $landing['categorie'])
                ->defaults('landing', $url)
                ->name('landing.'.$url);
        }
    }

    /*
     | Redirections permanentes — les URL ci-dessous sont indexees mais ne
     | sont plus canoniques.
     */
    foreach (config('seo_routes.accueil_aliases') as $alias) {
        Route::get('/'.$alias, fn () => redirect()->to(lien('accueil'), 301));
    }

    foreach (config('seo_routes.category_aliases') as $alias => $slug) {
        Route::get('/'.$alias, fn () => redirect()->to(lien('categorie', ['categorie' => $slug]), 301));
    }
};

/*
| Ultra-book : URL sans segment de langue.
|
| Ces routes portent les noms canoniques (`accueil`, `categorie`, …). Sur
| une marque multilingue, `ResoudreLangue` les intercepte et renvoie vers
| leur equivalent prefixe : une page n'a ainsi qu'une seule adresse par
| langue.
*/
Route::middleware(ResoudreLangue::class)->group(fn () => $portail());

/*
| Dustfolio : une copie des memes routes par langue servie, prefixee et
| nommee `<langue>.` — `en.accueil`, `fr.accueil`.
|
| La boucle est le point important : ajouter une langue se fait dans
| `config/langues.php` et dans la liste de la marque, sans toucher ici.
| (Tesli declare chaque route prefixee a la main, ce qui l'a conduit a en
| oublier au fil des ajouts.)
|
| `ForcerLangue` impose la langue du segment quel que soit le cookie : sur
| une URL qui porte sa langue, c'est l'URL qui fait foi.
*/
foreach (array_keys(config('langues.disponibles', [])) as $langue) {
    Route::prefix($langue)
        ->name($langue.'.')
        ->middleware(ForcerLangue::class.':'.$langue)
        ->group(fn () => $portail($langue));
}

/*
| Anciennes URL du legacy, en dernier : elles ne doivent capter que ce que
| les routes ci-dessus n'ont pas pris. Sans segment de langue — ce sont des
| adresses figees, citees telles quelles ailleurs.
*/
require __DIR__.'/redirections.php';

/*
| Toute URL restee sans route passe par le groupe `web` (session, auth) :
| la 404 affiche ainsi la meme barre de navigation, visiteur ou creatif
| connecte, que le reste du portail.
*/
Route::fallback(fn () => abort(404));
