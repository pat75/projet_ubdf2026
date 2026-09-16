<?php

/*
| Langues du portail.
|
| Le legacy les designait par un identifiant POSIX (`fr_FR`, `en_US`,
| `ja_JP`) parce que gettext en a besoin pour `setlocale`. Laravel n'en a
| pas besoin : la cle courte suffit, et c'est elle qui apparait dans les URL
| (`/fr`, `/en`, `/ja`), comme deja dans `.htaccess`.
|
| `posix` ne sert plus qu'a retrouver le catalogue d'origine a l'import et a
| remplir la balise `og:locale`.
*/

return [

    'defaut' => 'fr',

    'langues' => [
        'fr' => ['nom' => 'Français', 'posix' => 'fr_FR'],
        'en' => ['nom' => 'English', 'posix' => 'en_US'],
        'ja' => ['nom' => '日本語', 'posix' => 'ja_JP'],
    ],

    // Duree du choix de langue, reprise du legacy (setcookie + 90 jours).
    'cookie_jours' => 90,
];
