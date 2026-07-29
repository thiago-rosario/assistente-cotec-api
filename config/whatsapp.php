<?php

return [
    'incoming_idempotency_ttl' => (int) env('WHATSAPP_INCOMING_IDEMPOTENCY_TTL', 86400),
];
