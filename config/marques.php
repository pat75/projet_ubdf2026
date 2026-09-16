<?php

/*
| Marques servies par la plateforme.
|
| Le site de 2019 tenait la meme table dans `conf/conf_domaine_2018.php` et
| la parcourait ainsi :
|
|     if ( preg_match('/'.$key.'/i', $_SERVER['HTTP_HOST']) ) { … }
|
| La cle servait donc de **motif** : « ultra-book » reconnaissait n'importe
| quel hote qui contenait ces lettres, le point de « extra-book.net » valait
| n'importe quel caractere, et le premier motif qui mordait l'emportait —
| l'ordre du tableau faisait partie du comportement sans que rien ne le dise.
|
| Ici un hote est reconnu par **egalite**, le prefixe « www. » en moins.
| Un hote inconnu rend la marque par defaut plutot qu'une page blanche.
*/

return [

    'defaut' => 'ub',

    'marques' => [

        'ub' => [
            'nom' => 'Ultra-book',
            'email' => 'contact@ultra-book.net',
            'locale' => 'fr',

            // Suffixe du dossier de ressources (`image_dir` du legacy) :
            // /img_front pour Ultra-book, /img_front_df pour Dustfolio.
            'assets' => '',
            'logo' => '/img_front/ultra-book_logo_nb.svg',

            // Domaine canonique en production : c'est lui qui sert a
            // construire les URL absolues (courriels, sitemap, og:url).
            'canonique' => env('UB_CANONIQUE', 'https://www.ultra-book.com'),

            // Hotes reconnus. Le prefixe « www. » est retire avant
            // comparaison : www.ultra-book.com et ultra-book.com sont le
            // meme hote.
            'hotes' => array_filter([
                env('BOOK_DOMAIN', 'ubdf2026.ultra-book.name'),
                'ultra-book.com',
                'ultrabook.pro',
                'extra-book.net',
                'extra-book.info',
                'extra-book.be',
            ]),
        ],

        'df' => [
            'nom' => 'Dustfolio',
            'email' => 'contact@dustfolio.com',
            'locale' => 'fr',
            'assets' => '_df',
            'logo' => '/img_front_df/dustfolio.svg',

            'canonique' => env('DF_CANONIQUE', 'https://www.dustfolio.com'),

            'hotes' => array_filter([
                env('DF_DOMAIN', 'ubdf-dust-2026.ultra-book.name'),
                'dustfolio.com',
                'extra-book.biz',
            ]),
        ],
    ],

    /*
    | Libelles a substituer dans les contenus editoriaux servis sous une
    | marque secondaire.
    |
    | Dustfolio n'a jamais eu de contenu propre : le legacy rendait les pages
    | d'Ultra-book en y remplacant le nom au vol.
    |
    |     $cont_wp = preg_replace('/ultra-book/i', $inc_action->site_name, $cont_wp);
    |     $cont_wp = preg_replace('/POLYGUN/i', 'DustWare SAS', $cont_wp);
    */
    'substitutions' => [
        'df' => [
            'ultra-book' => 'Dustfolio',
            'POLYGUN' => 'DustWare SAS',
        ],
    ],

    /*
    | Etiquettes de sous-domaine qui ne peuvent jamais designer un book.
    |
    | Le legacy ne s'en preoccupait pas : rien n'empechait un creatif de
    | prendre le login « www » et de capter le sous-domaine correspondant.
    */
    'sous_domaines_reserves' => [
        'www', 'df', 'api', 'admin', 'mail', 'ftp', 'cdn', 'static',
        'assets', 'blog', 'magazine', 'dev', 'test', 'staging',
    ],
];
