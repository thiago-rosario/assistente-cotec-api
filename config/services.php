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

    'whatsapp' => [
        'sender' => env(
            'WHATSAPP_MESSAGE_SENDER',
            env('WHATSAPP_TRANSPORT', env('APP_ENV') === 'production' ? 'editacodigo' : 'log'),
        ),
    ],

    'editacodigo_bot' => [
        'webhook_url' => env('EDITACODIGO_BOT_WEBHOOK_URL', 'https://host.docker.internal:8443/'),
        'user' => env('EDITACODIGO_BOT_USER', 'editacodigo_user'),
        'token' => env('EDITACODIGO_BOT_TOKEN', ''),
        'timeout' => (int) env('EDITACODIGO_BOT_TIMEOUT', 15),
        'connect_timeout' => (int) env('EDITACODIGO_BOT_CONNECT_TIMEOUT', 3),
        'retry_times' => (int) env('EDITACODIGO_BOT_RETRY_TIMES', 3),
        'retry_delay_ms' => (int) env('EDITACODIGO_BOT_RETRY_DELAY_MS', 250),
        'retry_max_delay_ms' => (int) env('EDITACODIGO_BOT_RETRY_MAX_DELAY_MS', 2000),
        'verify_tls' => filter_var(env('EDITACODIGO_BOT_VERIFY_TLS', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true,
    ],

];
