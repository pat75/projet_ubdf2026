<?php

use App\Support\Marque;
use Illuminate\Support\Facades\Route as RouteFacade;

if (! function_exists('lien')) {
    /**
     * URL d'une route du portail, dans la langue courante.
     *
     * Les routes du portail existent en deux jeux : les noms canoniques
     * (`accueil`), sans segment de langue, et une copie prefixee par langue
     * (`en.accueil`, `fr.accueil`). Selon la marque servie, l'un ou l'autre
     * s'applique — Ultra-book est monolingue, Dustfolio porte la langue en
     * tete de chaque URL.
     *
     * `lien('accueil')` choisit le bon jeu. C'est la seule chose a retenir :
     * dans les vues et les controleurs du portail, `lien()` remplace
     * `route()`.
     *
     * `$parametres` accepte les memes formes que `route()` : un tableau,
     * un scalaire pour un parametre unique, ou un modele.
     */
    function lien(string $nom, mixed $parametres = [], bool $absolu = true): string
    {
        $marque = request()?->attributes->get('marque') ?? Marque::defaut();

        if ($marque->multilingue()) {
            $prefixe = app()->getLocale().'.';

            if (RouteFacade::has($prefixe.$nom)) {
                return route($prefixe.$nom, $parametres, $absolu);
            }
        }

        return route($nom, $parametres, $absolu);
    }
}
