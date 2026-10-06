{{--
 | Retouches d'aspect du back-office. Injectees dans le <head> du panneau
 | plutot que dans un theme Filament compile : une dizaine de lignes ne
 | justifient pas une seconde chaine de build Tailwind.
--}}
<style>
    /*
     | La colonne de navigation se detache du contenu : un gris tres leger
     | en mode clair, et en mode sombre l'inverse, une nuance plus claire
     | que le fond de page (qui est presque noir).
     */
    .fi-sidebar,
    .fi-sidebar-header {
        background-color: rgb(213 213 215); /* zinc-100 assombri de 20 %, puis eclairci de 10 % puis de 20 % */
    }

    .fi-sidebar {
        border-inline-end: 1px solid rgb(168 168 173);
    }

    /*
     | Libelles de la navigation en noir plein : sur ce gris, le zinc-600
     | d'origine manquait de contraste.
     */
    .fi-sidebar .fi-sidebar-nav .fi-sidebar-item-label,
    .fi-sidebar .fi-sidebar-nav .fi-sidebar-item-icon,
    .fi-sidebar .fi-sidebar-group-label,
    .fi-sidebar .fi-sidebar-group-collapse-button {
        color: rgb(0 0 0);
    }

    .fi-sidebar .fi-sidebar-nav .fi-sidebar-item-label {
        font-weight: 600;
    }

    .dark .fi-sidebar,
    .dark .fi-sidebar-header {
        /* Assombri de 20 % lui aussi, mais toujours plus clair que le fond
           de page, qui est presque noir : la colonne reste detachee. */
        background-color: rgb(31 31 34);
    }

    .dark .fi-sidebar {
        border-inline-end-color: rgb(63 63 70); /* zinc-700 */
    }

    /* En mode sombre, le noir plein serait illisible : on garde le blanc. */
    .dark .fi-sidebar .fi-sidebar-nav .fi-sidebar-item-label,
    .dark .fi-sidebar .fi-sidebar-nav .fi-sidebar-item-icon,
    .dark .fi-sidebar .fi-sidebar-group-label,
    .dark .fi-sidebar .fi-sidebar-group-collapse-button {
        color: rgb(244 244 245); /* zinc-100 */
    }

    /*
     | Barre d'outils des listes : la recherche a gauche, tout le reste
     | (filtres, colonnes, actions) cale a droite sur la meme ligne.
     |
     | Le bloc recherche/filtres/colonnes de Filament est dissous
     | (`display: contents`) pour que chaque element devienne un enfant de la
     | barre : la recherche prend la marge automatique, qui pousse le reste
     | contre le bord droit, et les actions passent en dernier.
     */
    .fi-ta-header-toolbar {
        justify-content: flex-start;
        align-items: center;
        gap: 30px;
    }

    .fi-ta-header-toolbar > :not(.fi-ta-actions) {
        display: contents;
    }

    .fi-ta-header-toolbar .fi-ta-search-field {
        order: -1;
        margin-inline-end: auto;
    }

    .fi-ta-header-toolbar > .fi-ta-actions {
        order: 1;
        margin-inline-start: 0;
        gap: 30px;
    }

    /*
     | Code promo deja utilise : toute la ligne hachuree, traits obliques
     | de 4px a 30 % d'opacite. Pose sur chaque cellule, dont le fond
     | masquerait sinon un degrade pose sur la ligne.
     */
    .ub-code-utilise > td {
        background-image: repeating-linear-gradient(
            45deg,
            rgb(107 114 128 / .3) 0,
            rgb(107 114 128 / .3) 4px,
            transparent 4px,
            transparent 8px
        );
    }

    /* Conversation indesirable : tout le texte de la ligne en rouge. */
    .ub-conversation-spam .fi-ta-text-item,
    .ub-conversation-spam .fi-ta-text-item * {
        color: rgb(220 38 38);
    }

    .dark .ub-conversation-spam .fi-ta-text-item,
    .dark .ub-conversation-spam .fi-ta-text-item * {
        color: rgb(248 113 113);
    }

    /*
     | Bloc « Encaisse » du tableau de bord
     | (resources/views/filament/widgets/encaissements.blade.php).
     */

        /* La carte occupe toute la hauteur de sa rangee, celle du graphique d'en face. */
        .ub-encaissements,
        .ub-encaissements > .fi-section {
            height: 100%;
        }

        .ub-encaissements > .fi-section {
            display: flex;
            flex-direction: column;
        }

        /* La grille ne peut faire toute la hauteur que si les deux
           enveloppes de la section la lui passent. */
        .ub-encaissements .fi-section-content-ctn {
            display: flex;
            flex: 1;
        }

        .ub-encaissements .fi-section-content {
            flex: 1;
        }

        .ub-encaissements-grille {
            display: grid;
            grid-template-columns: 1fr;
            /* Deux rangees de meme hauteur, qui se partagent la carte. */
            grid-auto-rows: 1fr;
            gap: .75rem;
            height: 100%;
        }

        @media (min-width: 640px) {
            .ub-encaissements-grille {
                grid-template-columns: 1fr 1fr;
            }
        }

        .ub-encaissement {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: .5rem;
            padding: .875rem 1rem;
            border-radius: .75rem;
            background-color: rgb(249 250 251);
            box-shadow: inset 0 0 0 1px rgb(9 9 11 / .06);
        }

        .ub-encaissement-tete {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .5rem;
        }

        .ub-encaissement-libelle {
            font-size: .6875rem;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: rgb(107 114 128);
        }

        .ub-encaissement-variation {
            display: inline-flex;
            flex-shrink: 0;
            align-items: center;
            gap: .1875rem;
            padding: .125rem .375rem;
            border-radius: .375rem;
            font-size: .6875rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .ub-encaissement-variation svg {
            width: .875rem;
            height: .875rem;
        }

        .ub-encaissement-variation.ub-hausse {
            background-color: rgb(220 252 231);
            color: rgb(21 128 61);
        }

        .ub-encaissement-variation.ub-baisse {
            background-color: rgb(255 237 213);
            color: rgb(194 65 12);
        }

        .ub-encaissement-montant {
            font-size: 1.5rem;
            font-weight: 600;
            line-height: 1.15;
            letter-spacing: -.015em;
            font-variant-numeric: tabular-nums;
            color: rgb(9 9 11);
        }

        .ub-encaissement-rappel {
            margin-top: .125rem;
            font-size: .75rem;
            font-variant-numeric: tabular-nums;
            color: rgb(107 114 128);
        }

        .dark .ub-encaissement {
            background-color: rgb(255 255 255 / .05);
            box-shadow: inset 0 0 0 1px rgb(255 255 255 / .1);
        }

        .dark .ub-encaissement-montant {
            color: rgb(255 255 255);
        }

        .dark .ub-encaissement-libelle,
        .dark .ub-encaissement-rappel {
            color: rgb(161 161 170);
        }

        .dark .ub-encaissement-variation.ub-hausse {
            background-color: rgb(74 222 128 / .12);
            color: rgb(134 239 172);
        }

        .dark .ub-encaissement-variation.ub-baisse {
            background-color: rgb(251 146 60 / .12);
            color: rgb(253 186 116);
        }
    /*
     | Tableau de bord : fonds des chiffres cles (ChiffresCles) et des
     | quatre cases du bloc « Encaisse », aux couleurs des metiers du
     | portail (core.css, .coultxt_*), meme teinte eclaircie pour que le
     | texte noir reste lisible.
     */
    .fi-wi-stats-overview-stat.ub-stat-creatifs { background-color: rgb(211 213 222); }  /* graphiste #757c98 */
    .fi-wi-stats-overview-stat.ub-stat-formules { background-color: rgb(219 219 228); }  /* moyenne graphiste #757c98 et architecte #a79fbc */
    .fi-wi-stats-overview-stat.ub-stat-demandes { background-color: rgb(227 224 234); }  /* architecte #a79fbc */
    .ub-encaissements .ub-encaissement-1 { background-color: rgb(223 225 234); }  /* webdesigner #9ca1bc */
    .ub-encaissements .ub-encaissement-2 { background-color: rgb(229 217 222); }  /* designer objet #af8897 */
    .ub-encaissements .ub-encaissement-3 { background-color: rgb(225 210 206); }  /* plasticien #a27365 */
    .ub-encaissements .ub-encaissement-4 { background-color: rgb(222 221 217); }  /* illustrateur jeunesse #979689 */

    .dark .fi-wi-stats-overview-stat.ub-stat-creatifs { background-color: rgb(117 124 152 / .28); }
    .dark .fi-wi-stats-overview-stat.ub-stat-formules { background-color: rgb(142 142 170 / .28); }
    .dark .fi-wi-stats-overview-stat.ub-stat-demandes { background-color: rgb(167 159 188 / .28); }
    .dark .ub-encaissements .ub-encaissement-1 { background-color: rgb(156 161 188 / .28); }
    .dark .ub-encaissements .ub-encaissement-2 { background-color: rgb(175 136 151 / .28); }
    .dark .ub-encaissements .ub-encaissement-3 { background-color: rgb(162 115 101 / .28); }
    .dark .ub-encaissements .ub-encaissement-4 { background-color: rgb(151 150 137 / .28); }

    /* Derniers creatifs inscrits : avatar colle a l'identifiant et au nom. */
    .ub-creatif { display: inline-flex; align-items: center; gap: .5rem; white-space: nowrap; }
    .ub-creatif-avatar { width: 32px; height: 32px; border-radius: 9999px; object-fit: cover; flex-shrink: 0; }
    .ub-creatif-login { font-weight: 600; }
    .ub-creatif-nom { color: rgb(107 114 128); }
    /*
     | Colonne avatar des listes (creatifs, visiteurs, derniers inscrits...) :
     | 8px exactement entre l'avatar et le texte de la colonne suivante.
     | Filament laisse 12px de chaque cote (24px en tout) : on retire le
     | remplissage cote avatar et cote texte, puis on pose les 8px.
     */
    .ub-cellule-avatar .fi-ta-image {
        padding-inline-end: 0;
    }

    .ub-cellule-avatar + .fi-ta-cell {
        padding-inline-start: 8px;
    }

    .ub-cellule-avatar + .fi-ta-cell :is(.fi-ta-col, .fi-ta-text, .fi-ta-text-item, .fi-ta-image) {
        padding-inline-start: 0;
    }

    /* Conversations : avatar colle a deux lignes de texte. */
    .ub-personne { display: inline-flex; align-items: center; gap: .5rem; }
    .ub-personne-avatar { width: 32px; height: 32px; border-radius: 9999px; object-fit: cover; flex-shrink: 0; }
    .ub-personne-textes { display: flex; flex-direction: column; line-height: 1.25; }
    .ub-personne-titre { font-weight: 500; }
    .ub-personne-dessous { font-size: .8125rem; color: rgb(107 114 128); }
    .dark .ub-personne-dessous { color: rgb(161 161 170); }

    /* Label « Createur » / « Visiteur » des dernieres desinscriptions. */
    .ub-label-compte { padding: 1px 8px; border-radius: 9999px; font-size: .75rem; font-weight: 600; }
    .ub-label-creatif { background: rgb(219 234 254); color: rgb(30 64 175); }
    .ub-label-visiteur { background: rgb(228 228 231); color: rgb(63 63 70); }
    .dark .ub-label-creatif { background: rgb(59 130 246 / .2); color: rgb(147 197 253); }
    .dark .ub-label-visiteur { background: rgb(255 255 255 / .12); color: rgb(212 212 216); }

    .dark .ub-creatif-nom { color: rgb(161 161 170); }
</style>
