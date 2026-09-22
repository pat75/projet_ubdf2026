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
        return route(nom_route($nom), $parametres, $absolu);
    }
}

if (! function_exists('nom_route')) {
    /**
     * Nom effectif d'une route du portail pour la marque et la langue
     * courantes : `accueil` sur Ultra-book, `en.accueil` sur Dustfolio.
     *
     * `lien()` suffit pour construire une URL. Ce helper existe pour les
     * cas ou c'est le **nom** qu'il faut, et non l'URL : une URL signee
     * (`URL::temporarySignedRoute`) doit viser la route prefixee, sinon
     * `ResoudreLangue` redirige vers la version prefixee et la signature,
     * calculee sur l'URL complete, ne correspond plus.
     */
    function nom_route(string $nom): string
    {
        $marque = request()?->attributes->get('marque') ?? Marque::defaut();

        if ($marque->multilingue()) {
            $prefixe = app()->getLocale().'.';

            if (RouteFacade::has($prefixe.$nom)) {
                return $prefixe.$nom;
            }
        }

        return $nom;
    }
}

if (! function_exists('wd_remove_accents')) {
    /**
     * Slug d'URL du legacy (inc/inc_user.php), repris a l'identique : les
     * gabarits de book construisent avec lui les liens `<titre>-r<id>-c<id>`
     * deja indexes par les moteurs. Le modifier changerait ces URL.
     */
    function wd_remove_accents($str, $charset = 'utf-8')
    {
        $str = mb_strtolower((string) $str, $charset);
        $str = htmlentities($str, ENT_NOQUOTES, $charset);
        $str = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml)\;#', '\1', $str);
        $str = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $str);
        $str = preg_replace('#\&[^;]+\;#', '_', $str);
        $str = preg_replace("#[ |/|'|\"|_|-]+#", '_', $str);
        $str = preg_replace("#[^a-z0-9\_\-]#", '_', $str);

        return trim($str, '_');
    }
}

/*
|--------------------------------------------------------------------------
| Fonctions globales du legacy appelees par les gabarits de book
|--------------------------------------------------------------------------
| Reprises de 2011_front/action_book.php. Les gabarits portes les appellent
| par leur nom d'origine.
*/

if (! function_exists('book_socializer')) {
    /** Titre et URL de la page, encodes pour les liens de partage. */
    function book_socializer($tmp_titre)
    {
        return [urlencode((string) $tmp_titre), urlencode(request()->fullUrl())];
    }
}

if (! function_exists('recursive_array_search')) {
    function recursive_array_search($needle, $haystack)
    {
        foreach ((array) $haystack as $key => $value) {
            if ($needle === $value || (is_array($value) && recursive_array_search($needle, $value) !== false)) {
                return $key;
            }
        }

        return false;
    }
}

if (! function_exists('book_actu_txt')) {
    /**
     * HTML d'une page de book.
     *
     * Les images inserees dans les pages sont referencees sous
     * `/users_2/<l>/<l>/<login>/cms_html/<fichier>` (ou cms_pref, img_).
     * Le legacy prefixait ces chemins par l'adresse du portail ; ils
     * designent maintenant le service d'images, qui sert l'original.
     */
    function book_actu_txt($tmp_txt, $abs_url = '')
    {
        $html = htmlspecialchars_decode((string) $tmp_txt, ENT_QUOTES);

        $prefixe = '#(src|href)="(?:https?://[^/"]+)?/users_2/[^/"]/[^/"]/([^/"]+)/';

        // Images de l'editeur : arborescence conservee.
        $html = preg_replace($prefixe.'img_cms/([^"]+)"#', '$1="/books/$2/cms/$3"', $html);

        // Autres dossiers du book (declinaisons, cms_pref) : l'original.
        return preg_replace($prefixe.'[^/"]+/([^/"]+)"#', '$1="/books/$2/source/$3"', $html);
    }
}
