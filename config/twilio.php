<?php

declare(strict_types=1);

return [
    'account_sid' => env('TWILIO_ACCOUNT_SID', env('TWILIO_SID')),
    'auth_token' => env('TWILIO_AUTH_TOKEN'),
    'whatsapp_from' => env('TWILIO_WHATSAPP_FROM', env('TWILIO_WHATSAPP_NUMBER')),
    'status_callback' => env('TWILIO_STATUS_CALLBACK_URL'),
    'validate_signature' => filter_var(
        env('TWILIO_VALIDATE_SIGNATURE', env('APP_ENV') === 'production'),
        FILTER_VALIDATE_BOOL
    ),
];
