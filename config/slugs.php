<?php

/*
| Segments d'URL traduits des pages du portail.
|
| Ultra-book (monolingue, sans prefixe) prend la version `fr`. Dustfolio
| sert chaque langue sous son prefixe avec son propre segment :
| `/en/create-a-book`, `/fr/creer-un-book`. Les autres segments d'une meme
| page redirigent (301) vers celui de la langue, pour qu'un lien colle
| d'une langue a l'autre ne tombe pas en 404.
*/

return [

    'inscription' => [
        'fr' => 'creer-un-book',
        'en' => 'create-a-book',
    ],

];
