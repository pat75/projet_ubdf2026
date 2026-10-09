<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirige vers le portail canonique tout hote de hasard sous nos domaines.
 *
 * Le joker DNS (*.ultra-book.com) envoie a l'application n'importe quel
 * hote, y compris a deux niveaux (www.remipepin.ultra-book.com). Faute de
 * route de book, il retombait sur le portail : pages construites avec des
 * URL absolues sur cet hote, mises en cache pour tous (images cassees sur
 * l'accueil), doublons indexables.
 *
 * Sont servis tels quels, sous chacun de nos domaines D :
 *   - D et www.D (portail) ;
 *   - <login>.D quand D est un domaine de books (un seul niveau).
 * Tout autre hote sous D recoit une 301 vers le portail de la marque, chemin
 * conserve. Un hote etranger a nos domaines (localhost, IP, tests) n'est
 * pas concerne.
 */
class HoteAutorise
{
    /** Serveur a serveur ou technique : jamais redirige. */
    private const LIBRES = ['payplug/notification', '.well-known/*', 'up'];

    public function handle(Request $request, Closure $next): Response
    {
        $hote = strtolower($request->getHost());

        if ($request->is(...self::LIBRES)) {
            return $next($request);
        }

        foreach (config('marques.marques') as $marque) {
            $domainesBooks = array_map('strtolower', array_filter([$marque['domaine_books'] ?? null]));

            foreach (array_unique([...array_map('strtolower', $marque['hotes']), ...$domainesBooks]) as $domaine) {
                if ($hote === $domaine || $hote === 'www.'.$domaine) {
                    return $next($request);
                }

                if (! str_ends_with($hote, '.'.$domaine)) {
                    continue;
                }

                $sousDomaine = substr($hote, 0, -strlen('.'.$domaine));

                if (in_array($domaine, $domainesBooks, true) && preg_match('/^[-a-z0-9]+$/', $sousDomaine)) {
                    return $next($request);
                }

                return redirect()->away($this->portail($marque).$request->getRequestUri(), 301);
            }
        }

        return $next($request);
    }

    /** Portail de la marque : canonique en production, premier hote ailleurs. */
    private function portail(array $marque): string
    {
        if (app()->isProduction() && ! empty($marque['canonique'])) {
            return rtrim($marque['canonique'], '/');
        }

        return 'https://'.$marque['hotes'][0];
    }
}
