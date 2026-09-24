<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Editeur de texte des pages
    |--------------------------------------------------------------------------
    |
    | App\Livewire\Espace\Pages (edition d'une page en accordeon) choisit
    | ici entre deux moteurs :
    |
    |   - 'redactor'       : Redactor 3.5.2 classique, contenu HTML dans
    |                        book_articles.body (voir resources/js/espace.js) ;
    |   - 'redactor_bloc'  : editeur par blocs maison, base sur Editor.js,
    |                        contenu structure dans book_articles.body_blocks
    |                        (voir resources/js/espace-blocs.js). Un rendu
    |                        HTML equivalent reste ecrit dans body a chaque
    |                        enregistrement (App\Services\Espace\RenduBlocsPage),
    |                        pour qu'un futur affichage public puisse lire
    |                        l'un ou l'autre sans migration de donnees.
    |
    | Reglage global : tous les createurs utilisent le meme moteur.
    */
    'editeur_texte' => env('EDITEUR_PAGES', 'redactor'),

];
