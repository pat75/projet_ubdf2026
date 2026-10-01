<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\Langue;
use App\Support\Marque;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Choix de langue du selecteur du menu (Dustfolio seulement).
 *
 * Retient le choix dans le cookie `ub_lang`, que `ResoudreLangue` fait
 * passer avant la langue du navigateur, puis renvoie vers la page dans la
 * langue choisie. Une marque monolingue n'a rien a choisir : 404.
 */
class ChoisirLangueController extends Controller
{
    public function __invoke(Request $request, string $langue): RedirectResponse
    {
        $marque = $request->attributes->get('marque') ?? Marque::defaut();

        abort_unless($marque->multilingue() && $marque->sert($langue), 404);

        return redirect()
            ->to($this->retour($request->query('retour'), $langue))
            ->withCookie(cookie(Langue::COOKIE, $langue, config('langues.cookie_jours', 90) * 24 * 60));
    }

    /**
     * Chemin local seulement : une URL absolue ou « //hote » ferait du
     * selecteur une redirection ouverte vers n'importe quel site.
     */
    private function retour(mixed $retour, string $langue): string
    {
        $prefixe = '/'.$langue;

        if (! is_string($retour) || ! str_starts_with($retour, $prefixe)) {
            return $prefixe;
        }

        $suite = substr($retour, strlen($prefixe));

        return $suite === '' || $suite[0] === '/' || $suite[0] === '?' ? $retour : $prefixe;
    }
}
