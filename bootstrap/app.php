<?php

use App\Http\Middleware\NormalizeUnicodeInput;
use App\Http\Middleware\ResoudreMarque;
use App\Support\Langue;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Toute entree utilisateur est ramenee a de l'UTF-8 normalise (NFC).
        $middleware->append(NormalizeUnicodeInput::class);

        // La marque (Ultra-book ou Dustfolio) se deduit de l'hote et
        // conditionne le nom du site, les books listes et les courriels.
        $middleware->append(ResoudreMarque::class);

        /*
         | Le cookie de langue reste en clair.
         |
         | Ce n'est pas un secret, et il doit rester lisible par autre chose
         | que Laravel : le JavaScript repris du front 2018 lit `lang`, et
         | le cookie est partage avec les books servis sur les sous-domaines.
         | Chiffre, il serait illisible pour eux — et indechiffrable pour
         | Laravel lui-meme s'il venait de l'ancien site.
         */
        /*
         | Il n'y a pas de page de connexion : le formulaire vit dans une
         | fenetre modale de l'accueil. Un visiteur envoye vers une page
         | protegee revient donc a l'accueil, la fenetre ouverte
         | (`connexion_ouverte`), et repart ensuite vers sa destination
         | grace a `redirect()->intended()`.
         */
        $middleware->redirectGuestsTo(function (Request $requete) {
            $requete->session()?->flash('connexion_ouverte', true);

            return lien('accueil');
        });

        $middleware->encryptCookies(except: [
            Langue::COOKIE,
            // Celui pose par le site de 2019, encore present chez les
            // visiteurs : chiffre, il serait illisible et son choix perdu.
            Langue::COOKIE_LEGACY,
        ]);

        /*
         | Le formulaire de contact du front 2018 est poste par un JavaScript
         | qui ne connait pas le jeton CSRF de Laravel, et qu'on ne reecrit
         | pas avant la phase 9.
         |
         | L'exemption est sans consequence ici : la route n'agit sur aucune
         | session — elle enregistre une demande d'un visiteur anonyme et
         | notifie le creatif. Il n'y a rien qu'un tiers puisse declencher au
         | nom de quelqu'un d'autre. Elle est protegee par le captcha et par
         | une limite de debit par adresse IP.
         |
         | Le fil de discussion, lui, reste sous CSRF : son formulaire est
         | rendu par Blade et porte le jeton.
         */
        $middleware->validateCsrfTokens(except: ['intermediate_send', 'payplug/notification']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         | 404 dediee pour un book absent ou supprime.
         |
         | Sur un sous-domaine de book, l'erreur generique de Laravel
         | (« The page you're looking for could not be found ») ne dit rien
         | du contexte au visiteur. On la remplace par une page qui nomme la
         | situation et renvoie vers le portail, uniquement quand l'hote est
         | un sous-domaine de book — le reste des 404 (portail, points
         | d'entree techniques) garde le rendu par defaut.
         */
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            $bookDomain = config('ubdf.book_domain');

            if (! $bookDomain || ! str_ends_with($request->getHost(), '.'.$bookDomain)) {
                return null;
            }

            return response()->view('book.introuvable', [
                'accueilPortail' => 'https://'.$bookDomain,
            ], 404);
        });
    })->create();
