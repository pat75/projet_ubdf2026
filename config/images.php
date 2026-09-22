<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Declinaisons
    |--------------------------------------------------------------------------
    |
    | Reprises telles quelles de `conf/conf_img.php` du site 2019, dimensions
    | verifiees sur les fichiers reels de `users_2/`. Le legacy pre-generait
    | ces neuf declinaisons **a l'enregistrement**, dans neuf dossiers par
    | book : 5 276 books x 9 dossiers, dont la plupart ne servaient jamais.
    | Elles sont desormais produites a la demande et mises en cache.
    |
    | `mode` :
    |   - `contain` : tient dans la boite, sans deformation ni agrandissement
    |     (c'est `recadr` vide dans le legacy) ;
    |   - `cover` : remplit la boite et rogne le debord (`recadr` 1 ou 2).
    |
    | La distinction a ete confirmee a la mesure : une source 1240x1754 donne
    | 34x48 en `img_ptf_small` (contain) mais 75x75 en `img_iph_small` (cover).
    */
    'declinaisons' => [

        // Original borne. Le legacy stockait deja la source redimensionnee.
        'source' => ['largeur' => 1980, 'hauteur' => 3600, 'mode' => 'contain', 'legacy' => 'img_'],

        // Vignettes de l'espace creatif.
        'adm_small' => ['largeur' => 24, 'hauteur' => 35, 'mode' => 'contain', 'legacy' => 'img_adm_small'],
        'adm_medium' => ['largeur' => 180, 'hauteur' => 180, 'mode' => 'contain', 'legacy' => 'img_adm_medium'],

        // Book public.
        'ptf_small' => ['largeur' => 48, 'hauteur' => 48, 'mode' => 'contain', 'legacy' => 'img_ptf_small'],
        'ptf_medium' => ['largeur' => 550, 'hauteur' => 3600, 'mode' => 'contain', 'legacy' => 'img_ptf_medium'],

        // Version mobile.
        'iph_small' => ['largeur' => 75, 'hauteur' => 75, 'mode' => 'cover', 'legacy' => 'img_iph_small'],
        'iph_medium' => ['largeur' => 320, 'hauteur' => 480, 'mode' => 'contain', 'legacy' => 'img_iph_medium'],

        // Cartes du portail.
        'front_desk' => ['largeur' => 250, 'hauteur' => 136, 'mode' => 'cover', 'legacy' => 'img_front_desk'],
        'front_mob' => ['largeur' => 140, 'hauteur' => 76, 'mode' => 'cover', 'legacy' => 'img_front_mob'],

        /*
         | Carres recadres des pages d'accueil de book. Le legacy les
         | demandait a phpThumb avec leurs dimensions dans l'URL
         | (`phpThumb.php?src=…&zc=1&w=368&h=368`) ; les gabarits n'en
         | utilisent que ces trois, declarees ici par leur nom.
         */
        'carre_368' => ['largeur' => 368, 'hauteur' => 368, 'mode' => 'cover', 'legacy' => 'phpThumb zc=1'],
        'carre_335' => ['largeur' => 335, 'hauteur' => 335, 'mode' => 'cover', 'legacy' => 'phpThumb zc=1'],
        'carre_183' => ['largeur' => 183, 'hauteur' => 183, 'mode' => 'cover', 'legacy' => 'phpThumb zc=1'],
    ],

    /*
    | Declinaison servie quand l'URL n'en precise aucune.
    */
    'defaut' => 'ptf_medium',

    /*
    | Qualite JPEG et WebP des declinaisons produites.
    */
    'qualite' => (int) env('IMAGES_QUALITE', 82),

    /*
    | Le cache des declinaisons, sur le disque `public`. Il se vide sans
    | dommage : tout s'y regenere depuis la source.
    */
    'cache' => 'cache_images',

    /*
    | Bornes de securite a l'entree du generateur.
    |
    | Le legacy passait par phpThumb, qui acceptait les dimensions dans
    | l'URL. C'etait sa faille : n'importe qui pouvait demander la
    | fabrication d'images arbitrairement grandes. Ici seules les
    | declinaisons nommees ci-dessus sont acceptees, et une source trop
    | lourde en pixels est refusee plutot que decompressee.
    */
    'pixels_max' => (int) env('IMAGES_PIXELS_MAX', 50_000_000),


    /*
    | Poids maximal d'un fichier envoye depuis l'espace creatif, en Ko. La
    | source est ensuite bornee a 1980x3600 puis reencodee.
    */
    'envoi_ko_max' => (int) env('IMAGES_ENVOI_KO_MAX', 20480),
];
