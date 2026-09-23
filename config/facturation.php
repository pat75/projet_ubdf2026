<?php

return [

    /*
    | Annuaire des entreprises de l'Etat (DINUM). API ouverte : ni compte
    | ni cle, sept appels par seconde et par adresse IP.
    | https://recherche-entreprises.api.gouv.fr/docs
    */
    'annuaire' => [
        'url' => env('ANNUAIRE_ENTREPRISES_URL', 'https://recherche-entreprises.api.gouv.fr'),
    ],

    /*
    | Categories juridiques de l'INSEE, limitees a celles que l'on
    | rencontre chez les createurs. Le code brut est affiche pour les
    | autres : mieux vaut un nombre qu'un libelle faux.
    */
    'formes_juridiques' => [
        '1000' => 'Entrepreneur individuel',
        '5202' => 'Société en nom collectif',
        '5306' => 'Société en commandite simple',
        '5410' => 'SARL nationale',
        '5415' => 'SARL d’économie mixte',
        '5426' => 'SARL immobilière',
        '5498' => 'SARL unipersonnelle (EURL)',
        '5499' => 'Société à responsabilité limitée (SARL)',
        '5505' => 'SA à participation ouvrière',
        '5510' => 'SA nationale',
        '5599' => 'Société anonyme (SA)',
        '5710' => 'Société par actions simplifiée (SAS)',
        '5720' => 'SAS unipersonnelle (SASU)',
        '5785' => 'Société d’exercice libéral par actions simplifiée',
        '6540' => 'Société civile immobilière',
        '6599' => 'Société civile',
        '9220' => 'Association déclarée',
        '9260' => 'Association de droit local',
    ],

];
