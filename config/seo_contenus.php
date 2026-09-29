<?php

/*
| Contenus editoriaux du referencement (SEO) et des moteurs generatifs (GEO).
|
| Premier jet a relire : ces textes sont visibles sur les pages. Ils ne
| doivent affirmer que ce qui est vrai sur la plateforme (pas de tarif, pas
| de chiffre invente) : les assistants IA les reprennent tels quels.
|
| - metiers : introduction de chaque page metier (2 ou 3 phrases).
| - faq : questions communes aux pages metier, `:metiers` (pluriel) et
|   `:metier` (singulier « freelance ») y sont remplaces.
| - `:marque` : nom de la marque servie (Ultra-book, Dustfolio). Traductions
|   anglaises dans lang/en.json.
| - landings : pages d'accroche (config/seo_routes.php). Chacune a son
|   titre, son H1, sa description et son introduction : sans eux, elles
|   dupliquaient la page metier et se concurrencaient dans Google.
*/

return [

    'metiers' => [
        'illustrateur' => "Illustration éditoriale, presse, affiche, packaging, bande dessinée : retrouvez ici la sélection des illustrateurs et illustratrices freelance de :marque. Parcourez leurs books, comparez leurs univers et contactez directement celui ou celle qui correspond à votre projet.",
        'illustrateur-jeunesse' => "Albums, romans jeunesse, manuels scolaires, jeux : la sélection des illustrateurs et illustratrices jeunesse freelance de :marque. Chaque book présente un univers graphique ; contactez directement l'artiste qui correspond à votre projet d'édition.",
        'graphiste' => "Identité visuelle, logo, mise en page, affiche, édition : la sélection des graphistes freelance de :marque. Parcourez leurs réalisations et contactez directement le graphiste indépendant qui correspond à votre besoin.",
        'directeur-artistique' => "Campagnes, identités de marque, direction de projets visuels : la sélection des directeurs et directrices artistiques freelance de :marque. Consultez leurs books et contactez-les directement.",
        'digital' => "Webdesign, UX/UI, sites, applications, motion : la sélection des webdesigners et développeurs freelance de :marque. Parcourez leurs projets et contactez directement le profil qui correspond à votre besoin.",
        'plasticien' => "Peinture, sculpture, installation, techniques mixtes : la sélection des artistes plasticiens de :marque. Découvrez leurs œuvres et contactez-les directement pour une commande, une exposition ou une collaboration.",
        'photographe' => "Portrait, mode, produit, reportage, architecture : la sélection des photographes freelance de :marque. Parcourez leurs books et contactez directement le photographe qui correspond à votre projet.",
        'design' => "Design produit, mobilier, objet : la sélection des designers freelance de :marque. Consultez leurs réalisations et contactez-les directement.",
        'architecte' => "Architecture, architecture intérieure, perspective et visualisation : la sélection des architectes indépendants de :marque. Parcourez leurs projets et contactez-les directement.",
    ],

    'faq' => [
        [
            'question' => 'Comment trouver un :metier freelance sur :marque ?',
            'reponse' => 'Parcourez la sélection par métier, ou lancez une recherche par mots-clés (style, technique, domaine) ou par nom. Chaque carte mène au book du créatif, avec ses projets, sa présentation et ses coordonnées.',
        ],
        [
            'question' => 'Comment contacter un :metier ?',
            'reponse' => 'Depuis son book, avec le formulaire de contact : votre message lui parvient directement, sans intermédiaire. Vous fixez ensuite ensemble le projet, le délai et le tarif.',
        ],
        [
            'question' => 'Comment les :metiers sont-ils sélectionnés ?',
            'reponse' => 'Tous les créatifs peuvent publier leur book sur :marque. Une sélection est faite tous les trois mois par des professionnels : ce sont les books retenus qui apparaissent en tête des pages métier.',
        ],
        [
            'question' => 'Je suis :metier : comment apparaître sur :marque ?',
            'reponse' => 'Créez gratuitement votre book en quelques minutes, ajoutez vos images et votre présentation : il est en ligne à votre adresse personnelle, et peut être retenu lors de la prochaine sélection.',
        ],
    ],

    'landings' => [
        'graphistes-freelance' => [
            'titre' => 'Graphistes freelance : trouvez votre graphiste indépendant',
            'h1' => 'Graphistes freelance',
            'description' => 'Trouvez un graphiste freelance pour votre identité visuelle, logo ou mise en page : parcourez les portfolios sélectionnés et contactez-le directement.',
            'intro' => "Vous cherchez un graphiste freelance pour un logo, une charte ou une plaquette ? Voici les portfolios des graphistes indépendants sélectionnés sur Ultra-book : comparez leurs réalisations et écrivez directement à celui qui vous convient.",
        ],
        'meilleurs-graphistes' => [
            'titre' => 'Les meilleurs graphistes freelance : la sélection',
            'h1' => 'Les meilleurs graphistes',
            'description' => 'La sélection des meilleurs graphistes freelance, choisis tous les trois mois par des professionnels : identité, édition, affiche. Consultez leurs books.',
            'intro' => "Tous les trois mois, des professionnels retiennent les books de graphistes les plus aboutis. Voici cette sélection : identité visuelle, édition, affiche, packaging.",
        ],
        'graphiste-independant' => [
            'titre' => 'Graphiste indépendant : portfolios et contact direct',
            'h1' => 'Graphiste indépendant',
            'description' => 'Travaillez avec un graphiste indépendant : parcourez les portfolios de graphistes freelance et contactez-les sans intermédiaire.',
            'intro' => "Travailler avec un graphiste indépendant, c'est un interlocuteur unique du brief à la livraison. Parcourez leurs books et contactez-les directement depuis leur page.",
        ],
        'webdesigner-developpeur-freelance' => [
            'titre' => 'Webdesigner et développeur freelance : portfolios',
            'h1' => 'Webdesigners et développeurs freelance',
            'description' => 'Webdesigners et développeurs freelance pour votre site ou votre application : parcourez leurs portfolios et contactez-les directement.',
            'intro' => "Pour un site, une application ou une refonte, trouvez un profil qui conçoit et développe. Voici les portfolios des webdesigners et développeurs freelance d'Ultra-book.",
        ],
        'webdesigner-freelance' => [
            'titre' => 'Webdesigner freelance : trouvez votre webdesigner',
            'h1' => 'Webdesigners freelance',
            'description' => 'Trouvez un webdesigner freelance pour votre site ou votre interface : parcourez les portfolios sélectionnés et contactez-le directement.',
            'intro' => "Maquettes de site, interfaces, UX/UI : voici les portfolios des webdesigners freelance d'Ultra-book. Comparez leurs projets et contactez directement celui qui vous correspond.",
        ],
        'developpeur-freelance' => [
            'titre' => 'Développeur web freelance : portfolios',
            'h1' => 'Développeurs web freelance',
            'description' => 'Trouvez un développeur web freelance : parcourez les portfolios des profils digitaux sélectionnés et contactez-les directement.',
            'intro' => "Intégration, développement, sites sur mesure : parcourez les portfolios des développeurs et profils digitaux freelance d'Ultra-book, et contactez-les directement.",
        ],
        'webdesign' => [
            'titre' => 'Webdesign : portfolios de webdesigners freelance',
            'h1' => 'Webdesign',
            'description' => 'Des projets de webdesign par des créatifs freelance : sites, interfaces, applications. Parcourez les portfolios et contactez leurs auteurs.',
            'intro' => "Sites, interfaces, applications : découvrez des projets de webdesign réalisés par les créatifs freelance d'Ultra-book, et contactez directement leurs auteurs.",
        ],
        'webdesigner' => [
            'titre' => 'Webdesigner : portfolios et contact direct',
            'h1' => 'Webdesigners',
            'description' => 'Choisissez un webdesigner à partir de son portfolio : projets de sites et d’interfaces, contact direct depuis son book.',
            'intro' => "Le meilleur moyen de choisir un webdesigner, c'est de voir ses projets. Parcourez leurs portfolios et contactez-les directement depuis leur book.",
        ],
        'illustrateur-freelance' => [
            'titre' => 'Illustrateur freelance : trouvez votre illustrateur',
            'h1' => 'Illustrateurs freelance',
            'description' => 'Trouvez un illustrateur freelance pour l’édition, la presse ou la communication : parcourez les portfolios sélectionnés et contactez-le directement.',
            'intro' => "Édition, presse, communication, packaging : voici les portfolios des illustrateurs et illustratrices freelance d'Ultra-book. Comparez leurs styles et écrivez directement à celui ou celle qui vous correspond.",
        ],
        'trouver-un-illustrateur' => [
            'titre' => 'Trouver un illustrateur : portfolios et contact direct',
            'h1' => 'Trouver un illustrateur',
            'description' => 'Trouvez l’illustrateur de votre projet : parcourez les portfolios par style, lancez une recherche et contactez-le directement.',
            'intro' => "Pour trouver un illustrateur, partez de son travail : parcourez les portfolios ci-dessous, ou recherchez un style ou une technique. Chaque book permet de contacter directement l'artiste.",
        ],
        'comment-trouver-un-illustrateur' => [
            'titre' => 'Comment trouver un illustrateur ? Méthode et portfolios',
            'h1' => 'Comment trouver un illustrateur ?',
            'description' => 'Comment trouver un illustrateur : définir le style, comparer les portfolios, préparer son brief et le contacter. Méthode et sélection de books.',
            'intro' => "1. Définissez le style recherché et l'usage (édition, presse, packaging). 2. Comparez les portfolios ci-dessous ou recherchez un style par mots-clés. 3. Préparez un brief court : sujet, format, délai, budget. 4. Contactez l'illustrateur depuis son book.",
        ],
        'meilleurs-illustrateurs' => [
            'titre' => 'Les meilleurs illustrateurs freelance : la sélection',
            'h1' => 'Les meilleurs illustrateurs',
            'description' => 'La sélection des meilleurs illustrateurs freelance, choisis tous les trois mois par des professionnels. Découvrez leurs books.',
            'intro' => "Tous les trois mois, des professionnels retiennent les books d'illustrateurs les plus remarquables. Voici cette sélection, toutes techniques confondues.",
        ],
        'trouver-une-illustratrice' => [
            'titre' => 'Trouver une illustratrice : portfolios d’illustratrices',
            'h1' => 'Trouver une illustratrice',
            'description' => 'Trouvez une illustratrice freelance pour votre projet : parcourez les portfolios et contactez-la directement depuis son book.',
            'intro' => "Parcourez les portfolios des illustratrices et illustrateurs freelance d'Ultra-book, comparez leurs univers et contactez directement l'artiste qui correspond à votre projet.",
        ],
        'illustrateur-graphiste' => [
            'titre' => 'Illustrateur graphiste : portfolios freelance',
            'h1' => 'Illustrateurs graphistes',
            'description' => 'Illustrateurs graphistes freelance, entre image et mise en page : parcourez leurs portfolios et contactez-les directement.',
            'intro' => "Certains projets demandent à la fois l'image et la mise en page. Voici les portfolios d'illustrateurs qui pratiquent aussi le graphisme.",
        ],
        'meilleurs-illustrateurs-jeunesse' => [
            'titre' => 'Les meilleurs illustrateurs jeunesse : la sélection',
            'h1' => 'Les meilleurs illustrateurs jeunesse',
            'description' => 'La sélection des meilleurs illustrateurs jeunesse freelance : albums, romans, manuels. Découvrez leurs books et contactez-les.',
            'intro' => "Albums, romans, manuels scolaires : voici la sélection des illustrateurs et illustratrices jeunesse retenus par des professionnels, tous les trois mois.",
        ],
    ],

];
