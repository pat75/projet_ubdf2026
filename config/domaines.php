<?php

/*
 | Noms de domaine personnels achetes par les createurs (Ma formule).
 | Voir _doc/_doc_VPS/03_achat_domaine.md.
 */
return [

    // Extensions proposees => prix TTC pour un an (euros). TVA 20 %.
    'extensions' => [
        'fr' => 16.00,
        'com' => 19.00,
    ],

    'tva' => 0.20,

    /*
     | simulation : aucun appel OVH (local sans cles, tests).
     | dry-run    : vrais appels OVH jusqu'a la validation a blanc du panier,
     |              jamais de commande.
     | reel       : commande definitive.
     */
    'ovh_mode' => env('OVH_MODE', 'simulation'),

    // Version des textes acceptes (CGU, renonciation) : gardee avec la preuve.
    'version_consentement' => '2026-10',

    /*
     | Pays proposes pour le titulaire (code ISO => nom). Les pays de
     | l'Union europeenne (cles de `ue`) seuls peuvent detenir un .fr
     | (regle Afnic).
     */
    'pays' => [
        'FR' => 'France', 'BE' => 'Belgique', 'CH' => 'Suisse', 'LU' => 'Luxembourg',
        'MC' => 'Monaco', 'CA' => 'Canada', 'DE' => 'Allemagne', 'ES' => 'Espagne',
        'IT' => 'Italie', 'PT' => 'Portugal', 'NL' => 'Pays-Bas', 'AT' => 'Autriche',
        'IE' => 'Irlande', 'GB' => 'Royaume-Uni', 'US' => 'États-Unis',
        'PF' => 'Polynésie française', 'NC' => 'Nouvelle-Calédonie', 'RE' => 'La Réunion',
        'GP' => 'Guadeloupe', 'MQ' => 'Martinique', 'GF' => 'Guyane', 'YT' => 'Mayotte',
        'MA' => 'Maroc', 'TN' => 'Tunisie', 'SN' => 'Sénégal', 'TW' => 'Taïwan', 'JP' => 'Japon',
    ],

    // Union europeenne (et DROM, francais) : titulaires admis pour un .fr.
    'ue' => ['FR', 'BE', 'LU', 'DE', 'ES', 'IT', 'PT', 'NL', 'AT', 'IE', 'RE', 'GP', 'MQ', 'GF', 'YT',
        'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'GR', 'HU', 'LV', 'LT', 'MT', 'PL', 'RO', 'SK', 'SI', 'SE'],
];
