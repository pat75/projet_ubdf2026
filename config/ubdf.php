<?php

return [

    /*
     * Domaine racine des books creatifs.
     * Chaque book est servi sur <login>.<book_domain>.
     */
    'book_domain' => env('BOOK_DOMAIN', 'ubdf2026.ultra-book.name'),

    /*
     * Racine des fichiers books du projet 2019 (lecture seule).
     * Utilisee uniquement par les commandes de migration.
     */
    'legacy_books_path' => env('LEGACY_BOOKS_PATH'),

    /*
     * Racine du site 2019 lui-meme (lecture seule), d'ou sont repris les
     * medias de l'ancien WordPress du magazine.
     */
    'legacy_path' => env('LEGACY_PATH', '/Users/pat/Sites_2019/_projet_ubdf_2020'),

    /*
     * Nombre de comptes importes en developpement.
     */
    'dev_users_sample' => (int) env('DEV_USERS_SAMPLE', 100),

];
