<?php

return [
    'incoming_idempotency_ttl' => (int) env('WHATSAPP_INCOMING_IDEMPOTENCY_TTL', 86400),
    'conversation_state_ttl' => (int) env('WHATSAPP_CONVERSATION_STATE_TTL', 1800),
];
