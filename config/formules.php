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

        // Promotions : elles remplacent l'option indiquee quand elles
        // s'appliquent (voir App\Services\Paiement\Promotions).
        10 => ['libelle' => '6 mois, promotion du jour', 'ttc' => 11.90, 'ht' => 9.91, 'mois' => 6, 'barre' => 21.90,
            'promo' => 'promo-auto-6mois', 'remplace' => [1]],
        30 => ['libelle' => 'Formule 12 mois, Black Friday', 'ttc' => 22.00, 'ht' => 18.33, 'mois' => 12, 'barre' => 36.80,
            'promo' => 'blackfriday', 'remplace' => [2, 3]],
    ],

    /*
    | Black Friday : debut (Europe/Paris) => duree en jours. Historique du
    | legacy, suivi des deux annees suivantes sur le rythme de 2024 (lundi de
    | la semaine precedente, 12 jours). A ajuster chaque annee.
    */
    'black_friday' => [
        '2019-11-28' => 4, '2020-11-27' => 4, '2021-11-23' => 8, '2022-11-23' => 6,
        '2023-11-16' => 11, '2024-11-18' => 12, '2025-11-17' => 12, '2026-11-16' => 12,
    ],

    /*
    | Promo auto 6 mois (createurs n'ayant jamais paye) : premiere offre
    | 2 mois apres l'inscription, puis tous les 3 mois ; chaque offre vaut
    | 24 heures.
    */
    'promo_6_mois' => ['premiere_apres_mois' => 2, 'intervalle_mois' => 3, 'validite_heures' => 24],

    /*
    | Plafonds de chaque formule, repris de $conf_formule de
    | conf/conf_site.php : nombre de visuels, poids total en kilo-octets,
    | nombre de pages, et visuels par rubrique.
    */
    'limites' => [
        'gratuite' => ['visuels' => 12, 'poids_ko' => 22000, 'pages' => 8, 'visuels_par_rubrique' => 3],
        'payante' => ['visuels' => 500, 'poids_ko' => 120000, 'pages' => 500, 'visuels_par_rubrique' => 24],
    ],

    'payplug' => [
        // Cle secrete : sk_test_… en local, sk_live_… en production.
        'secret_key' => env('PAYPLUG_SECRET_KEY'),
    ],
];
