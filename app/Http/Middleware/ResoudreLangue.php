<?php

namespace App\Http\Middleware;

use App\Support\Langue;
use App\Support\Marque;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Determine la langue d'affichage.
 *
 * Ordre de preference, du plus explicite au plus general :
 *
 *   1. le parametre `lang` de la requete — la bascule du selecteur ;
 *   2. le cookie, ou le visiteur a laisse son choix precedent ;
 *   3. la session, pour la duree de la visite ;
 *   4. l'en-tete Accept-Language du navigateur ;
 *   5. la langue par defaut de la marque.
 *
 * Le legacy s'arretait au point 3 puis retombait sur le defaut : un
 * visiteur japonais arrivait en francais tant qu'il n'avait pas trouve le
 * selecteur, alors que son navigateur annoncait sa langue.
 */
class ResoudreLangue
{
    /** Repris ici pour rester referencable depuis bootstrap/app.php. */
    public const COOKIE_NOM = Langue::COOKIE;


    public function handle(Request $request, Closure $next): Response
    {
        $marque = $request->attributes->get('marque') ?? Marque::defaut();

        $langue = Langue::normaliser($request->query('lang'))
            ?? Langue::normaliser($request->cookie(Langue::COOKIE))
            ?? Langue::normaliser($request->session()->get(Langue::COOKIE))
            ?? Langue::depuisNavigateur($request->getLanguages())
            ?? $marque->locale;

        app()->setLocale($langue);

        $request->attributes->set('langue', $langue);
        $request->session()->put(Langue::COOKIE, $langue);

        View::share('langue', $langue);

        return $next($request);
    }
}
