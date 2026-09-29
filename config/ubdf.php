<?php

return [

    /*
     * Domaine racine des books creatifs.
     * Chaque book est servi sur <login>.<book_domain>.
     */
    'book_domain' => env('BOOK_DOMAIN', 'ubdf2026.ultra-book.name'),

    // Hote du portail : sert a cantonner le back-office.
    'portail_domain' => env('PORTAIL_DOMAIN', 'ubdf2026.ultra-book.name'),

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


    /*
    | Statuts professionnels proposes sur la fiche du createur
    | (`$conf['us_statut']` de conf/conf_site.php du legacy). La reprise a
    | garde le libelle en clair dans `users.status`, pas l'indice : les
    | anciennes fiches portent encore des nombres, que le formulaire
    | remplace au premier enregistrement.
    */
    'statuts' => [
        'A définir', 'Maison des artistes', 'Auto-entrepreneur', 'Salarié',
        'Étudiant', 'Eurl', 'Sarl/Sas', 'Freelance', 'Autre',
    ],

    /*
    | Editeur, tel qu'il figure sur les factures (html_pages_v2018/tpl/
    | ub_content_facture__*.tlp.php du legacy).
    */
    'editeur' => [
        'raison_sociale' => 'POLYGUN',
        'adresse' => "51 rue d'Hauteville\n75010 Paris",
        'mentions' => 'SIRET 479 054 553 00017 - APE 722 C - R.C.S Paris B 479 054 553',
        'tva_intra' => 'FR31479054553',
    ],
];
