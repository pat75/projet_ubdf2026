<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\Langue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Bascule de langue : /fr, /en, /ja.
 *
 * Le legacy renvoyait ces URL sur `action.php?lang=fr_FR`, qui affichait
 * l'accueil : changer de langue depuis une fiche de book faisait perdre la
 * page consultee. Ici le choix est enregistre et le visiteur revient d'ou
 * il vient.
 */
class LangueController extends Controller
{
    public function __invoke(Request $request, string $langue): RedirectResponse
    {
        abort_unless(Langue::supportee($langue), 404);

        $request->session()->put(Langue::COOKIE, $langue);

        return redirect()
            ->to($this->retour($request))
            ->withCookie(cookie(
                name: Langue::COOKIE,
                value: $langue,
                minutes: (int) config('langues.cookie_jours') * 24 * 60,
            ));
    }

    /**
     * Page d'ou vient le visiteur, si elle appartient bien au site.
     *
     * Rediriger vers un `Referer` sans le verifier ouvrirait une
     * redirection vers un domaine tiers.
     */
    private function retour(Request $request): string
    {
        $referer = (string) $request->headers->get('referer');

        if ($referer !== '' && parse_url($referer, PHP_URL_HOST) === $request->getHost()) {
            return $referer;
        }

        return route('accueil');
    }
}
