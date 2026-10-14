<?php

/*
 | CORS ferme : l'API sert des applications natives (mobile, desktop), qui
 | ne passent pas par les regles CORS d'un navigateur. Aucune page web
 | d'un autre site n'a a l'appeler. Par defaut, Laravel ouvrait api/* a
 | toutes les origines.
 |
 | Le jour ou une application web (autre origine) doit l'appeler : ajouter
 | son origine exacte a allowed_origins, jamais « * ».
 */
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
