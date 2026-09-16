<?php

namespace App\Http\Middleware;

use App\Support\Marque;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Determine la marque de la requete a partir de l'hote, et la rend
 * disponible partout : aux controleurs (`$request->attributes`), aux vues
 * (`$marque`) et aux courriels (`config('app.name')`).
 */
class ResoudreMarque
{
    public function handle(Request $request, Closure $next): Response
    {
        $marque = Marque::depuisHote($request->getHost());

        // Les controleurs lisent deja `brand` : la valeur vient desormais de
        // l'hote et non plus d'un defaut code en dur.
        $request->attributes->set('brand', $marque->code);
        $request->attributes->set('marque', $marque);

        View::share('marque', $marque);

        // Le nom du site apparait dans les titres, les courriels et les
        // gabarits repris du front 2018.
        config([
            'app.name' => $marque->nom,
            'mail.from.address' => $marque->email,
            'mail.from.name' => $marque->nom,
        ]);

        // Langue par defaut de la marque. Sur une marque multilingue, le
        // segment de langue de l'URL la remplacera (ForcerLangue).
        app()->setLocale($marque->locale());

        return $next($request);
    }
}
