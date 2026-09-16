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
    ],

];
