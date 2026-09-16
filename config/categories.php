<?php

/*
 * Categories metier du portail.
 *
 * Le legacy stocke le metier en texte libre dans inc_user.us_type, avec
 * 30 variantes pour 13 metiers (accents, anglais, casse, tirets). Ce fichier
 * fait autorite : "list" definit les categories, "legacy_map" ramene chaque
 * variante rencontree en base ub2020 vers un slug.
 */

return [

    /*
     * Chaque categorie porte trois libelles distincts, comme dans le front
     * 2018 : le nom du metier, le titre du bloc d'accueil (« Illustration »
     * pour les illustrateurs, « Art » pour les plasticiens) et la forme
     * employee dans « Derniere selection <...> freelance ».
     *
     * « accueil » donne l'ordre des blocs de la page d'accueil ; les
     * categories sans cette cle restent accessibles par leur URL mais
     * n'apparaissent pas sur l'accueil, comme dans le legacy.
     */
    'list' => [
        ['slug' => 'illustrateur',          'name' => 'Illustrateur',          'name_plural' => 'illustrateurs',
         'titre_bloc' => 'Illustration',           'freelance' => 'illustrateur',          'accueil' => 1],
        ['slug' => 'illustrateur-jeunesse', 'name' => 'Illustrateur jeunesse', 'name_plural' => 'illustrateurs jeunesse',
         'titre_bloc' => 'Illustration jeunesse',  'freelance' => 'illustrateur jeunesse', 'accueil' => 2],
        ['slug' => 'graphiste',             'name' => 'Graphiste',             'name_plural' => 'graphistes',
         'titre_bloc' => 'Graphisme',              'freelance' => 'graphiste',             'accueil' => 3],
        ['slug' => 'directeur-artistique',  'name' => 'Directeur artistique',  'name_plural' => 'directeurs artistiques',
         'titre_bloc' => 'Direction artistique',   'freelance' => 'directeur artistique',  'accueil' => 4],
        ['slug' => 'digital',               'name' => 'Webdesigner',           'name_plural' => 'webdesigners',
         'titre_bloc' => 'Digital & développement', 'freelance' => 'digital',              'accueil' => 5],
        ['slug' => 'plasticien',            'name' => 'Plasticien',            'name_plural' => 'plasticiens',
         'titre_bloc' => 'Art',                    'freelance' => 'plasticien',            'accueil' => 6],
        ['slug' => 'photographe',           'name' => 'Photographe',           'name_plural' => 'photographes',
         'titre_bloc' => 'Photographie',           'freelance' => 'photographe',           'accueil' => 7],
        ['slug' => 'design',                'name' => 'Designer',              'name_plural' => 'designers',
         'titre_bloc' => 'Design objet',           'freelance' => 'designer objet',        'accueil' => 8],
        ['slug' => 'architecte',            'name' => 'Architecte',            'name_plural' => 'architectes',
         'titre_bloc' => 'Architecture',           'freelance' => 'architecte',            'accueil' => 9],

        // Presentes dans l'annuaire et par leur URL, absentes de l'accueil.
        ['slug' => 'styliste',              'name' => 'Styliste',              'name_plural' => 'stylistes',
         'titre_bloc' => 'Stylisme',               'freelance' => 'styliste'],
        ['slug' => 'scenographe',           'name' => 'Scenographe',           'name_plural' => 'scenographes',
         'titre_bloc' => 'Scenographie',           'freelance' => 'scenographe'],
        ['slug' => 'modele',                'name' => 'Modele',                'name_plural' => 'modeles',
         'titre_bloc' => 'Modele',                 'freelance' => 'modele'],
        ['slug' => 'autre',                 'name' => 'Autre',                 'name_plural' => 'autres',
         'titre_bloc' => 'Autre',                  'freelance' => 'creatif'],
    ],

    /*
     * Cle = valeur brute de us_type (comparaison insensible a la casse),
     * valeur = slug cible. Toute valeur absente retombe sur "autre".
     */
    'legacy_map' => [
        'graphiste' => 'graphiste',
        'graphic' => 'graphiste',

        'illustrateur' => 'illustrateur',
        'illustrator' => 'illustrateur',

        'illustrateur jeunesse' => 'illustrateur-jeunesse',
        'illustrateur-jeunesse' => 'illustrateur-jeunesse',
        'youth illustrator' => 'illustrateur-jeunesse',

        'photographe' => 'photographe',
        'photographer' => 'photographe',

        'plasticien' => 'plasticien',
        'artist' => 'plasticien',

        'architecte' => 'architecte',
        'architect' => 'architecte',

        'design' => 'design',
        'designer objet' => 'design',
        'designer object' => 'design',

        'directeur artistique' => 'directeur-artistique',
        'directeur-artistique' => 'directeur-artistique',
        'artistic director' => 'directeur-artistique',

        'styliste' => 'styliste',
        'stylist' => 'styliste',

        'digital' => 'digital',
        'web' => 'digital',
        'webdesigner' => 'digital',
        'web designer' => 'digital',

        'scenographe' => 'scenographe',
        'scénographe' => 'scenographe',

        'modele' => 'modele',
        'modèle' => 'modele',

        'autre' => 'autre',
        'other' => 'autre',
    ],

    /*
     * Themes de book. Le legacy melange slugs et libelles FR dans
     * inc_user_pref.us_pf_version_web : meme principe de normalisation.
     */
    'legacy_theme_map' => [
        '' => 'mdl_default',
        'mdl_default' => 'mdl_default',
        'modèle classique' => 'mdl_2015_classique',
        'mdl_2015_classique' => 'mdl_2015_classique',
        'mdl_2015_grid' => 'mdl_2015_grid',
        'mdl_2016_zoom' => 'mdl_2016_zoom',
        'mdl_2014_responsive' => 'mdl_2014_responsive',
        'modèle portfolio 2014-responsive' => 'mdl_2014_responsive',
        'modèle portfolio 2012' => 'mdl_2012',
        'modèle portfolio 2012-slide' => 'mdl_2012_slide',
        'modèle portfolio 2013-pinter' => 'mdl_2013_pinter',
        'mdl_2020_ultra_frais' => 'mdl_2020_ultra_frais',
        'mdl_2020_ultra_zen' => 'mdl_2020_ultra_zen',
        'mdl_2012' => 'mdl_2012',
        'mdl_2012_slide' => 'mdl_2012_slide',
        'mdl_2013_pinter' => 'mdl_2013_pinter',
        'mdl_classique' => 'mdl_2015_classique',
        'modelo clásico' => 'mdl_2015_classique',
        'portfolio model 2013-pinter' => 'mdl_2013_pinter',
    ],

];
