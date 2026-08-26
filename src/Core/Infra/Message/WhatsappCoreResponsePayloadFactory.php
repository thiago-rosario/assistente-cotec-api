<?php

declare(strict_types=1);

namespace App\Core\Infra\Message;

class WhatsappCoreResponsePayloadFactory
{
    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function empty(string $intent, string $reply, array $filters = []): array
    {
        return [
            'reply' => $reply,
            'intent' => $intent,
            'total' => 0,
            'data' => [],
            'filters' => $filters,
        ];
    }
}
