<?php

declare(strict_types=1);

namespace App\Core\Conversation\Infra\Message;

class WhatsappResponsePayloadFactory
{
    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function empty(string $intent, string $reply): array
    {
        return [
            'reply' => $reply,
            'intent' => $intent,
            'total' => 0,
            'data' => [],
            'filters' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function withRecords(string $intent, array $filters, array $result, string $reply): array
    {
        return [
            'reply' => $reply,
            'intent' => $intent,
            'total' => $result['total'],
            'data' => $result['data'],
            'filters' => $filters,
        ];
    }
}
