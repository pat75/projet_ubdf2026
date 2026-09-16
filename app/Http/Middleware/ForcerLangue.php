<?php

namespace App\Http\Middleware;

use App\Support\Langue;
use App\Support\Marque;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Impose la langue d'une route prefixee, quel que soit le cookie du visiteur.
 *
 * Principe repris de Tesli (`ForceLocale`) : sur une URL qui porte sa langue,
 * **c'est l'URL qui fait foi**. Sans cela, `/en/illustrator` s'afficherait en
 * francais pour un visiteur dont le cookie dit « fr », et un moteur de
 * recherche indexerait la page anglaise avec un contenu francais.
 *
 * Le segment est refuse si la marque ne sert pas cette langue : Ultra-book
 * est monolingue, `/en/…` n'y existe pas.
 */
class ForcerLangue
{
    public function handle(Request $request, Closure $next, string $langue): Response
    {
        $marque = $request->attributes->get('marque') ?? Marque::defaut();

        abort_unless(Langue::supportee($langue) && $marque->sert($langue), 404);

        // Une marque monolingue n'a pas d'URL prefixee, meme dans sa propre
        // langue : ce serait une seconde adresse pour la meme page.
        abort_unless($marque->multilingue(), 404);

        app()->setLocale($langue);

        $request->attributes->set('langue', $langue);

        return $next($request);
    }
}
