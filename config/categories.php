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

    'list' => [
        ['slug' => 'graphiste',             'name' => 'Graphiste',             'name_plural' => 'Graphistes'],
        ['slug' => 'illustrateur',          'name' => 'Illustrateur',          'name_plural' => 'Illustrateurs'],
        ['slug' => 'illustrateur-jeunesse', 'name' => 'Illustrateur jeunesse', 'name_plural' => 'Illustrateurs jeunesse'],
        ['slug' => 'photographe',           'name' => 'Photographe',           'name_plural' => 'Photographes'],
        ['slug' => 'plasticien',            'name' => 'Plasticien',            'name_plural' => 'Plasticiens'],
        ['slug' => 'architecte',            'name' => 'Architecte',            'name_plural' => 'Architectes'],
        ['slug' => 'design',                'name' => 'Designer',              'name_plural' => 'Designers'],
        ['slug' => 'directeur-artistique',  'name' => 'Directeur artistique',  'name_plural' => 'Directeurs artistiques'],
        ['slug' => 'styliste',              'name' => 'Styliste',              'name_plural' => 'Stylistes'],
        ['slug' => 'digital',               'name' => 'Webdesigner',           'name_plural' => 'Webdesigners'],
        ['slug' => 'scenographe',           'name' => 'Scenographe',           'name_plural' => 'Scenographes'],
        ['slug' => 'modele',                'name' => 'Modele',                'name_plural' => 'Modeles'],
        ['slug' => 'autre',                 'name' => 'Autre',                 'name_plural' => 'Autres'],
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
