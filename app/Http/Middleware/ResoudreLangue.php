<?php

namespace App\Http\Middleware;

use App\Support\Langue;
use App\Support\Marque;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Langue des URL **sans** segment de langue.
 *
 * Sur une marque monolingue (Ultra-book), il n'y a rien a choisir : c'est
 * sa langue, toujours.
 *
 * Sur une marque multilingue (Dustfolio), une URL sans segment est une URL
 * incomplete : le visiteur est renvoye vers la meme page prefixee, dans la
 * langue la plus probable. Cela garantit qu'une seule adresse existe par
 * page et par langue, ce qui est la raison d'etre du prefixe.
 *
 * Ordre de preference pour deviner cette langue, repris de Tesli :
 * cookie, puis session, puis `Accept-Language`, puis la langue par defaut
 * de la marque. Le legacy s'arretait a la session : un visiteur anglophone
 * arrivait en francais alors que son navigateur annoncait sa langue.
 */
class ResoudreLangue
{
    public function handle(Request $request, Closure $next): Response
    {
        $marque = $request->attributes->get('marque') ?? Marque::defaut();

        if (! $marque->multilingue()) {
            app()->setLocale($marque->locale());
            $request->attributes->set('langue', $marque->locale());
            View::share('langue', $marque->locale());

            return $next($request);
        }

        $langue = $this->deviner($request, $marque);

        return redirect()->to($this->versionPrefixee($request, $langue), 302);
    }

    private function deviner(Request $request, Marque $marque): string
    {
        $candidats = [
            $request->cookie(Langue::COOKIE),
            $request->cookie(Langue::COOKIE_LEGACY),
            $request->session()->get(Langue::COOKIE),
        ];

        foreach ($candidats as $candidat) {
            $code = Langue::normaliser(is_string($candidat) ? $candidat : null);

            if ($code !== null && $marque->sert($code)) {
                return $code;
            }
        }

        // Coupe en developpement (config/langues.php) : la version de dev
        // reste dans la langue par defaut de la marque.
        if (! config('langues.navigateur')) {
            return $marque->locale();
        }

        return Langue::depuisNavigateur($request->getLanguages(), $marque->langues)
            ?? $marque->locale();
    }

    /** Meme page, meme parametres, avec le segment de langue en tete. */
    private function versionPrefixee(Request $request, string $langue): string
    {
        $chemin = trim($request->path(), '/');
        $chemin = $chemin === '/' ? '' : $chemin;

        $url = $request->getSchemeAndHttpHost().'/'.$langue.($chemin !== '' ? '/'.$chemin : '');

        return $request->getQueryString() ? $url.'?'.$request->getQueryString() : $url;
    }
}
