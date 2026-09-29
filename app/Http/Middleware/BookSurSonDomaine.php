<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Marque;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un book n'est servi que sur le domaine des books de la marque de son
 * compte (users.brand) : login.ultra-book.com pour un compte Ultra-book,
 * login.dustfolio.com pour un compte Dustfolio. Demande sur l'autre domaine,
 * il y renvoie (301), chemin et parametres conserves.
 *
 * Fournit aussi le parametre de domaine aux URL des routes du book
 * (route('book.contact.envoyer', ['login' => …])), sans le repeter partout.
 */
class BookSurSonDomaine
{
    public function handle(Request $requete, Closure $suite): Response
    {
        $route = $requete->route();
        $login = (string) $route->parameter('login');
        $domaine = (string) $route->parameter('domaineBooks');

        URL::defaults(['domaineBooks' => $domaine]);

        $marque = User::query()->where('login', $login)->value('brand');
        $attendu = $marque ? Marque::depuisCode($marque)->domaineBooks : null;

        if ($attendu && strcasecmp($attendu, $domaine) !== 0) {
            $cible = $requete->getScheme().'://'.$login.'.'.$attendu.$requete->getRequestUri();

            return redirect()->away($cible, 301);
        }

        // Le domaine ne sert qu'a l'aiguillage : les controleurs du book ne
        // le recoivent pas en argument.
        $route->forgetParameter('domaineBooks');

        return $suite($requete);
    }
}
