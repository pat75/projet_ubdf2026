<?php

/*
 * URL du portail heritees du site de 2020.
 *
 * Elles sont indexees depuis des annees : chacune doit continuer de repondre,
 * soit directement, soit par une redirection permanente vers l'URL canonique.
 * Source : le .htaccess du projet 2019 (~300 regles).
 */

return [

    /*
     * Alias renvoyant tous vers l'accueil. Le legacy en servait cinq
     * distinctes avec le meme contenu, ce qui dilue le referencement :
     * une seule est canonique, les autres redirigent en 301.
     */
    'accueil_aliases' => [
        // « recherche » a quitte cette liste : c'est desormais une page a
        // part entiere (/recherche?q=…), plus une redirection.
        'portfolios', 'portfolio-freelance', 'rechercher',
        'recherche-mots-cles', 'rechercher-des-portfolios',
    ],

    /*
     * Landings SEO : URL dediee -> categorie ciblee. Le legacy passait en
     * plus un identifiant de variante (« graphiste-1 ») qui ne changeait que
     * le titre de la page.
     */
    'landings' => [
        'graphistes-freelance' => ['categorie' => 'graphiste', 'variante' => 1],
        'meilleurs-graphistes' => ['categorie' => 'graphiste', 'variante' => 2],
        'graphiste-independant' => ['categorie' => 'graphiste', 'variante' => 3],
        'webdesigner-developpeur-freelance' => ['categorie' => 'digital', 'variante' => 1],
        'webdesigner-freelance' => ['categorie' => 'digital', 'variante' => 2],
        'developpeur-freelance' => ['categorie' => 'digital', 'variante' => 3],
        'webdesign' => ['categorie' => 'digital', 'variante' => 4],
        'webdesigner' => ['categorie' => 'digital', 'variante' => 5],
        'illustrateur-freelance' => ['categorie' => 'illustrateur', 'variante' => 1],
        'trouver-un-illustrateur' => ['categorie' => 'illustrateur', 'variante' => 2],
        'comment-trouver-un-illustrateur' => ['categorie' => 'illustrateur', 'variante' => 3],
        'meilleurs-illustrateurs' => ['categorie' => 'illustrateur', 'variante' => 4],
        'trouver-une-illustratrice' => ['categorie' => 'illustrateur', 'variante' => 5],
        'illustrateur-graphiste' => ['categorie' => 'illustrateur', 'variante' => 6],
        'meilleurs-illustrateurs-jeunesse' => ['categorie' => 'illustrateur-jeunesse', 'variante' => 1],
    ],

    /*
     * Anciennes URL de categorie -> slug canonique. Le legacy acceptait
     * plusieurs orthographes par metier ; elles redirigent desormais.
     */
    'category_aliases' => [
        'illustration' => 'illustrateur',
        'graphisme' => 'graphiste',
        'photo' => 'photographe',
        'photographes' => 'photographe',
        'designer' => 'design',
        'designer-objet' => 'design',
        'dispo' => 'illustrateur',
    ],

];
