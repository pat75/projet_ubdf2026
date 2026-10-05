<?php

return [

    /*
     | Types de demande du formulaire intermedie.
     |
     | Les cles sont celles postees par le JavaScript du front 2018
     | (`action`), lui-meme aligne sur les valeurs stockees dans
     | `ub2_intermediate_form.mf_action`. Elles ne peuvent pas changer sans
     | rompre la continuite avec les 5 228 demandes de ub2020.
     */
    'demandes' => [
        'work_A_contact' => [
            'libelle' => 'Prise de contact',
            'visuel' => false,
        ],
        'work_B_similary' => [
            'libelle' => 'Demande de travail similaire',
            'visuel' => true,
        ],
        'work_C_buy' => [
            'libelle' => 'Achat d’une image',
            'visuel' => true,
        ],
    ],

    /*
     | Duree de validite d'un lien de conversation. Le legacy n'en posait
     | aucune : un lien emis en 2019 ouvrait encore le fil en 2026.
     */
    'lien_valide_jours' => 180,

    /*
     | Nombre de demandes acceptees par adresse IP et par heure.
     */
    'demandes_par_heure' => 5,

    /*
     | Listes anti-spam reprises de `conf/conf_contact.php` (ub2020).
     |
     | Le legacy les gardait en chaines separees par des espaces et les
     | interrogeait avec `preg_match('/'.$mail.'/', $liste)` : la valeur
     | saisie servait de **motif**, et la liste de sujet. Une adresse d'une
     | lettre correspondait donc a tout, et un « / » dans la saisie rompait
     | l'expression. La liste d'IP, elle, comparait `$mail` au lieu de `$ip`
     | — le fichier d'origine porte d'ailleurs la mention « no active ».
     |
     | Ici ce sont des listes, comparees par egalite.
     */
    'spam' => [
        'emails' => [
        'pages.lorene@hotmail.fr',
        'adi@ndmails.com',
        'rolandj415@gmail.com',
        'pasqal.moniac@gmail.com',
        'pasqal2.moniac@gmail.com',
        'remy.chanson@dr.com',
        'setheithei@bestmailonline.com',
        'robin@merci-facteur.com',
        'matthieu.facteur@gmail.com',
        'torwiki.biz@torwiki.biz',
        'syttsgfgs@gmail.com',
        'feedbackform@make-success.com',
        'anatoledonarier@gmail.com',
        'nozdrinaleksej27@gmail.com',
        'les.montreurs@orange.fr',
        'judbayneoranderson@gmail.com',
        'laurent.pasquier830@gmail.com',
        'p8clement@outlook.fr',
        'fabgrenier1@outlook.fr',
        'mathieu.bi@outlook.fr',
        'coiffurestyff6@gmail.com',
        'fabrice.rutyl@outlook.com',
        'lop.chrii@gmail.com',
        'immensejoie@outlook.fr',
        'nicolas.dutard007@outlook.com',
        'partenairesassocies@yahoo.com',
        'lafamillebethencourt@outlook.com',
        'stephane-du971@hotmail.com',
        'ricardo-du974@hotmail.com',
        'pascalduboi103@gmail.com',
        'star.restaurant@outlook.de',
        'clementphilippe0002@outlook.fr',
        'jacques-paillard11@hotmail.com',
        'regismengin@outlook.fr',
        'olivierlesaout@outlook.fr',
        'rogermoffet@outlook.fr',
        'gueganmichel66@gmail.com',
        'worldthebestofficial@outlook.fr',
        'fbouvier23@gmail.com',
        'a.holat@outlook.fr',
        'clement.chan2@hotmail.com',
        'thierry.varichon@outlook.com',
        'concordehotel@outlook.fr',
        'walterlandry@outlook.fr',
        'bati-sarl972@hotmail.com',
        'restaurantstar@outlook.com',
        'jean.kerleau@outlook.fr',
        'giovanni.Gauthier@outlook.com',
        'gozalo.fabrice1@outlook.fr',
        'bert.romain@hotmail.com',
        'recrutement.urgent@outlook.com',
        'schmidtrestaurant8@gmail.com',
        'houpert.lorraine.etoile@outlook.fr',
        'olivierjacquemard@hotmail.com',
        'guillaume.maza02@outllook.fr',
        'berrouiguetsamia1991@gmail.com',
        'victoireperrot04800@gmail.com',
        'info@wawegraphisme.com',
        'paullabbe517@gmail.com',
        'duchesnelaurent20@gmail.com',
        'jacqueslauren8@gmail.com',
        'JubaM@outlook.fr',
        'mardonroland@gmail.com',
        'dupirejustin44@gmail.com',
        'garagejosevi@gmail.com',
        'davidsarlgarage@gmail.com',
        'garageolivier7@gmail.com',        ],

        'ips' => [
        '104.129.19.53',
        '185.85.162.242',
        '46.185.69.208',
        '5.188.210.2',
        '5.188.84.231',
        '91.205.168.60',
        '193.106.240.94',
        '93.5.116.85',
        '45.83.90.248',
        '45.83.90.249',
        '194.59.249.248',
        '197.234.219.19',
        '188.122.82.146',        ],

        // Une adresse de cette liste n'est jamais consideree comme un spam.
        'liste_blanche' => [
        'assistante.communication@fondationbrigittebardot.fr',        ],

        // Recherchees dans le corps du message, sans tenir compte de la casse.
        'expressions' => [
        'Obeliva 5mg',
        'FeedbackForm2019',
        'your Instagram username',
        'PlayAmo Casino',
        'отдохнуть',
        'socialadr.com',
        'mail.ru',
        'sending one million messages',
        'консультации',
        'studiomerliniortodonzia',
        'Online casino',
        'Buy RESURGE',
        'win jackpot',
        'regamega',
        'covid-monitor',
        'loveawake',
        'Top Casino',
        'agro-vista.ru',
        'free VPN',
        'FREE DEMO AVAILABLE',
        'IMMEDIATE PAYMENT',
        'BAR RESTAURANT DES AMIS Laurent Duchesne',
        'Jacques Lauren',
        'GARAGE JOSE',
        'GARAGE DAVID',
        'GARAGE OLIVIER',        ],

        // Au-dela de ce nombre de demandes de la meme adresse sur la periode,
        // le message est marque. Valeurs du legacy : 8 sur 48 heures.
        'seuil_repetition' => 8,
        'fenetre_heures' => 48,
    ],

    /*
     | Detection IA complementaire (config('messagerie.spam_filter.active')) :
     | interroge Jev (TypeSafe, via OpenRouter) sur le premier
     | message d'une conversation, au fil de l'affichage de la messagerie du
     | createur (App\Livewire\Espace\Messages::analyser), avec une seule question calibree —
     | « est-ce probablement un spam ? » — et affiche un label si la
     | probabilite depasse le seuil.
     |
     | Ne remplace pas la detection par regles ci-dessus (spam.*) : celle-ci
     | masque la demande (is_spam), la detection IA se contente de la
     | signaler (spam_ia sur la conversation), sans jamais rien masquer.
     */
    'spam_filter' => [
        'active' => (bool) env('MESSAGERIE_SPAM_FILTER', true),
        // Alternatives Jev (TypeSafe), essayees dans l'ordre. « api » dit
        // par quelle porte d'OpenRouter passer : « decisions » (probabilite
        // calibree, /api/alpha/decisions) ou « chat » (/chat/completions,
        // reponse JSON). Si aucune ne repond, l'analyse est suspendue
        // (`pause` minutes) et le back-office le signale.
        'modeles' => [
            ['modele' => '~typesafe/jev-latest', 'api' => 'decisions'],
            ['modele' => 'typesafe/jev-router', 'api' => 'chat'],
        ],
        'pause' => 30,
        // Probabilite calibree a partir de laquelle le label s'affiche.
        'seuil' => 0.6,
    ],
];
