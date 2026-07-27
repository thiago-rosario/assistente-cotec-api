<?php

return [
    'transport' => env('WHATSAPP_TRANSPORT', 'editacodigo_http'),

    'incoming_idempotency_ttl' => (int) env('WHATSAPP_INCOMING_IDEMPOTENCY_TTL', 86400),
];
