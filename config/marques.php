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

            /*
            | Ultra-book est **monolingue**. Ses URL n'ont pas de segment de
            | langue, et une URL prefixee n'existe pas : c'est ce qui evite
            | de publier deux adresses pour une meme page francaise.
            */
            'langues' => ['fr'],

            // Suffixe du dossier de ressources (`image_dir` du legacy) :
            // /img_front pour Ultra-book, /img_front_df pour Dustfolio.
            'assets' => '',
            'logo' => '/img_front/ultra-book_logo_nb.svg',

            // Version claire, pour les fonds sombres (pied de page).
            'logo_clair' => '/img_front/ultra-book_logo_nb_inverse.svg',
            // Image de partage par defaut (Open Graph / X), 1200 x 630.
            'image_partage' => '/img_front/partage/ultra-book.png',

            // Domaine des books : <login>.<domaine_books>. Un book n'est servi
            // que sur le domaine de la marque de son compte (users.brand) ;
            // sur celui de l'autre marque, il y redirige (301).
            'domaine_books' => env('BOOK_DOMAIN', 'ubdf2026.ultra-book.name'),

            // Domaine canonique en production : c'est lui qui sert a
            // construire les URL absolues (courriels, sitemap, og:url).
            'canonique' => env('UB_CANONIQUE', 'https://www.ultra-book.com'),

            // Titre et description par defaut. Ultra-book etant monolingue,
            // ils sont ecrits en francais, sans passer par __().
            'titre' => 'Portfolios freelance, illustrateur, graphiste, créer son book | Ultra-book',
            // 160 caracteres au plus : au-dela, Google tronque l'extrait.
            'description' => 'Portfolios de créatifs freelance : illustrateurs, graphistes, photographes, directeurs artistiques. Trouvez et contactez le bon indépendant sur Ultra-book.',

            // Hotes reconnus. Le prefixe « www. » est retire avant
            // comparaison : www.ultra-book.com et ultra-book.com sont le
            // meme hote.
            'hotes' => array_filter([
                env('BOOK_DOMAIN', 'ubdf2026.ultra-book.name'),
                'ultra-book.com',
                // Domaine de test de la mise en production.
                'extra-book.com',
                'extra-book.net',
                'extra-book.info',
                'extra-book.be',
            ]),
        ],

        'df' => [
            'nom' => 'Dustfolio',
            'email' => 'contact@dustfolio.com',

            /*
            | Dustfolio est **multilingue**, et anglophone par defaut. La
            | premiere langue de la liste est celle vers laquelle pointe la
            | racine du site. Chaque page porte sa langue en tete d'URL
            | (/en/illustrator, /fr/illustrateur), ce qui donne une adresse
            | distincte et indexable par langue.
            |
            | Les trois langues supplementaires prevues s'ajoutent ici.
            */
            'langues' => ['en', 'fr'],
            'assets' => '_df',
            'logo' => '/img_front_df/dustfolio.svg',
            'logo_clair' => '/img_front_df/dustfolio_b.svg',
            'image_partage' => '/img_front/partage/dustfolio.png',
            'domaine_books' => env('DF_BOOK_DOMAIN', 'ubdf-dust-2026.ultra-book.name'),

            'canonique' => env('DF_CANONIQUE', 'https://www.dustfolio.com'),

            /*
            | Dustfolio a ses propres accroches, relevees sur le site en
            | production : ce ne sont pas celles d'Ultra-book traduites.
            | Elles passent par __() pour suivre la langue de l'URL.
            */
            'titre' => 'Dustfolio, create an online portfolio, book and online portfolio',
            'description' => 'Dustfolio allows you to create your portfolio, add your images, captions, web links, presentation texts, and above all customize it.',

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
