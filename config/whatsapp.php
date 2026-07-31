<?php

return [
    'incoming_idempotency_ttl' => (int) env('WHATSAPP_INCOMING_IDEMPOTENCY_TTL', 86400),
    'message_sender' => env(
        'WHATSAPP_MESSAGE_SENDER',
        env('WHATSAPP_TRANSPORT', env('APP_ENV') === 'production' ? 'editacodigo' : 'log'),
    ),
];
