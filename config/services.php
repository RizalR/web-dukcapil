<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'siprima_whistleblowing' => [
        'url' => env('SIPRIMA_WHISTLEBLOWING_API_URL'),
        'token' => env('SIPRIMA_WHISTLEBLOWING_API_TOKEN'),
        'connect_timeout' => env('SIPRIMA_WHISTLEBLOWING_CONNECT_TIMEOUT', 3),
        'timeout' => env('SIPRIMA_WHISTLEBLOWING_API_TIMEOUT', 10),
        'stale_after_minutes' => env('SIPRIMA_WHISTLEBLOWING_STALE_AFTER_MINUTES', 30),
    ],

];
