<?php

/*
| Langues disponibles sur la plateforme.
|
| Meme convention que Tesli (`app.available_locales`) : code court => libelle
| affiche dans le selecteur. Le code court est celui qui apparait dans les
| URL.
|
| `posix` ne sert qu'a deux choses : retrouver le catalogue gettext d'origine
| a l'import, et remplir la balise `og:locale`.
|
| Ajouter une langue se fait ici, puis dans la liste `langues` de la marque
| concernee (`config/marques.php`) : les routes prefixees sont produites par
| une boucle, il n'y a rien a declarer route par route.
*/

return [

    'disponibles' => [
        'fr' => ['nom' => 'Français', 'posix' => 'fr_FR'],
        'en' => ['nom' => 'English', 'posix' => 'en_US'],

        // Mis de cote : le catalogue existe (630 chaines reprises de
        // ja_JP) mais la langue n'est pas ouverte. Les trois langues
        // supplementaires prevues pour Dustfolio viendront ici.
        // 'ja' => ['nom' => '日本語', 'posix' => 'ja_JP'],
    ],

    // Duree du choix de langue, reprise du legacy (setcookie + 90 jours).
    'cookie_jours' => 90,
];
