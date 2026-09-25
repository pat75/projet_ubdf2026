<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | OpenRouter : point d'entree unique pour toutes les operations IA du
    | site (correction de message, et ce qui suivra), via
    | App\Services\IA\OpenRouterModelSelector.
    */
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
    ],

    /*
    | reCAPTCHA v3 - protege la connexion, l'inscription et la demande de
    | mot de passe oublie.
    |
    | La cle publique etait ecrite en dur dans le gabarit ; elle passe ici.
    | Sans `secret`, la verification est **desactivee** et non pas reputee
    | reussie a l'aveugle : App\Services\Auth\Recaptcha le signale dans les
    | logs. C'est le cas en local et en test, ou aucun appel reseau ne doit
    | partir.
    */
    'recaptcha' => [
        'key' => env('RECAPTCHA_KEY', '6Lc8O5IUAAAAAJer15iYwddEROZzZnnIVzQe4P_1'),
        'secret' => env('RECAPTCHA_SECRET'),
        'score_minimum' => (float) env('RECAPTCHA_SCORE_MIN', 0.3),

        /*
         | Version 2 (case a cocher), utilisee par le formulaire de contact des
         | books : c'est celle de leurs gabarits d'origine. Cle distincte de
         | la v3 du portail.
         */
        'v2' => [
            'key' => env('RECAPTCHA_V2_KEY', '6Lc2eRQTAAAAAL1to7OJL12n4xLQE9i8O-i9G4Pn'),
            'secret' => env('RECAPTCHA_V2_SECRET'),
        ],
    ],


    /*
    | Google Maps, pour la carte du formulaire de contact et des themes.
    | La cle etait ecrite en dur dans trois gabarits du legacy.
    */
    /*
    | Connexion et creation de book via Google (Socialite). Client OAuth
    | propre a Ultra-book. L'adresse de retour n'est pas fixee ici : elle
    | suit l'hote de la requete (Ultra-book ou Dustfolio), et chacune doit
    | etre declaree dans la console Google.
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => '/auth/google/callback',
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_KEY'),
    ],

    /*
    | Offre couplee avec les-illustrateurs.com : les deux sites calculent
    | le meme code personnel a partir de l'identifiant et de l'annee, avec
    | cette cle partagee. Elle etait ecrite en clair dans le gabarit du
    | legacy (`$secretKey = "UBdiff"`) ; la changer invalide les codes
    | deja communiques, et il faut alors la changer des deux cotes.
    */
    'diffusion' => [
        'url' => env('DIFFUSION_URL', 'https://www.les-illustrateurs.com'),
        'cle' => env('DIFFUSION_CLE', 'UBdiff'),
    ],

];
