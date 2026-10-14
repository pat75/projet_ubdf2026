<?php

namespace App\Http\Middleware;

use App\Models\Domaine;
use App\Support\Marque;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Passerelle des domaines personnels.
 *
 * 1. Le VPS (Caddy) relaie « jeanne-dupont.fr » vers ce site en posant
 *    X-Forwarded-Host et un secret partage (X-Proxy-Secret). La requete est
 *    alors traitee comme si elle visait le sous-domaine du book
 *    (jeanne.<domaine des books>) : routes, marque et book s'appliquent
 *    sans autre changement. Les URL generees (liens, canonique, sitemap du
 *    book) repassent sur le domaine perso ; les images restent servies par
 *    le sous-domaine (/books/…), sans transiter par le VPS.
 *
 * 2. Une page demandee directement sur le sous-domaine d'un book qui a un
 *    domaine perso actif est redirigee (301) vers lui : une seule adresse
 *    indexee.
 *
 * Sans le bon secret, X-Forwarded-Host est ignore : n'importe qui peut
 * l'envoyer, seul le VPS connait le secret.
 *
 * Pose en tete des middlewares globaux, avant HoteAutorise et
 * ResoudreMarque, qui lisent l'hote.
 */
class ServirDomainePerso
{
    /** Chemins servis par le sous-domaine meme quand un domaine perso est actif (images). */
    private const SOUS_DOMAINE = ['books/*'];

    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('ubdf.proxy_secret');
        $hoteRelaye = (string) $request->headers->get('X-Forwarded-Host');

        if ($secret !== '' && $hoteRelaye !== '' && hash_equals($secret, (string) $request->headers->get('X-Proxy-Secret'))) {
            return $this->relayer($request, $next, Domaine::normaliser($hoteRelaye));
        }

        if ($redirection = $this->versDomainePerso($request)) {
            return $redirection;
        }

        return $next($request);
    }

    private function relayer(Request $request, Closure $next, string $nom): Response
    {
        $domaine = Domaine::servis()->with('user')->where('nom', $nom)->first();

        // Le domaine est verifie par Caddy avant tout certificat ; un domaine
        // retire entre-temps tombe simplement en 404.
        abort_if(! $domaine?->user || $domaine->user->trashed(), 404);

        $hoteBook = $domaine->user->login.'.'.Marque::depuisCode((string) $domaine->user->brand)->domaineBooks;

        $request->headers->set('Host', $hoteBook);
        $request->server->set('HTTP_HOST', $hoteBook);
        $request->server->set('HTTPS', 'on');
        $request->attributes->set('domaine_perso', $domaine->nom);

        URL::formatHostUsing(fn (string $racine, ?Route $route) => parse_url($racine, PHP_URL_HOST) === $hoteBook
            && ! str_starts_with((string) $route?->getName(), 'book.media')
                ? 'https://'.$domaine->nom
                : $racine);

        return $next($request);
    }

    private function versDomainePerso(Request $request): ?Response
    {
        if (! $request->isMethod('GET') || $request->is(...self::SOUS_DOMAINE)) {
            return null;
        }

        $hote = strtolower($request->getHost());

        foreach (Marque::domainesBooks() as $domaineBooks) {
            if (! str_ends_with($hote, '.'.strtolower($domaineBooks))) {
                continue;
            }

            $login = substr($hote, 0, -strlen('.'.$domaineBooks));
            $nom = Domaine::query()
                ->where('statut', 'actif')
                ->whereHas('user', fn ($q) => $q->where('login', $login))
                ->value('nom');

            return $nom ? redirect()->away('https://'.$nom.$request->getRequestUri(), 301) : null;
        }

        return null;
    }
}
