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
     | Recherche et selecteur de colonnes cales a gauche.
     |
     | Filament les pousse a droite (la barre est en `space-between`), a
     | l'oppose du regard qui balaie le tableau : sur une liste de plusieurs
     | milliers de creatifs, on cherche dix fois pour une action groupee.
     | On rend la barre alignee a gauche et on remonte le bloc
     | recherche/filtres/colonnes devant les actions groupees.
     */
    .fi-ta-header-toolbar {
        justify-content: flex-start;
        gap: 30px;
    }

    /* Le bloc recherche + filtres + colonnes passe devant les actions. */
    .fi-ta-header-toolbar > :not(.fi-ta-actions) {
        order: -1;
        display: flex;
        align-items: center;
        gap: 30px;
    }

    /*
     | Filtres, tri et export prennent le meme ecart que le reste de la
     | ligne. La marge automatique de Filament est annulee : elle poussait
     | les actions contre le bord droit, et aucun ecart fixe n aurait tenu.
     */
    .fi-ta-header-toolbar > .fi-ta-actions {
        margin-inline-start: 0;
        gap: 30px;
    }
</style>