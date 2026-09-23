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
        background-color: rgb(244 244 245); /* zinc-100 */
    }

    .fi-sidebar {
        border-inline-end: 1px solid rgb(228 228 231); /* zinc-200 */
    }

    .dark .fi-sidebar,
    .dark .fi-sidebar-header {
        background-color: rgb(39 39 42); /* zinc-800 */
    }

    .dark .fi-sidebar {
        border-inline-end-color: rgb(63 63 70); /* zinc-700 */
    }
</style>
