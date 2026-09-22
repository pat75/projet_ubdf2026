<?php

/*
|--------------------------------------------------------------------------
| Formules payantes
|--------------------------------------------------------------------------
|
| Grille en vigueur dans conf/conf_site.php du legacy ($conf_formule[1]).
| Les cles sont les numeros d'option du legacy : elles figurent dans les
| metadonnees des paiements Payplug deja passes.
|
| `reabonnement` : l'option 3 remplace la 2 pour un createur qui a deja
| paye au moins une fois, comme dans le legacy.
*/

return [
    'options' => [
        1 => ['libelle' => 'Formule 6 mois', 'ttc' => 21.90, 'ht' => 18.25, 'mois' => 6],
        2 => ['libelle' => 'Formule 12 mois', 'ttc' => 36.80, 'ht' => 30.67, 'mois' => 12, 'barre' => 43.80],
        3 => ['libelle' => 'Formule 12 mois, réabonnement', 'ttc' => 29.80, 'ht' => 24.83, 'mois' => 12, 'barre' => 36.80, 'reabonnement' => true],
        4 => ['libelle' => 'Pack Luxe 12 mois', 'ttc' => 118.00, 'ht' => 98.33, 'mois' => 12],
        5 => ['libelle' => 'Pack Site', 'ttc' => 428.00, 'ht' => 342.40, 'mois' => 12],
    ],

    'payplug' => [
        // Cle secrete : sk_test_… en local, sk_live_… en production.
        'secret_key' => env('PAYPLUG_SECRET_KEY'),
    ],
];
