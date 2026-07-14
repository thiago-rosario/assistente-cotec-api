<?php

declare(strict_types=1);

namespace App\Core\Conversation\Infra\Parser;

use App\Core\Conversation\Application\Interfaces\Parser\PythonBridgeEventParserInterface;
use Illuminate\Support\Arr;
use JsonException;

class PythonBridgeEventParser implements PythonBridgeEventParserInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function parse(string $line): ?array
    {
        $line = trim($line);

        if (! str_starts_with($line, '{')) {
            return null;
        }

        try {
            $event = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (! is_array($event) || Arr::get($event, 'type') !== 'received_message') {
            return null;
        }

        $payload = Arr::get($event, 'payload');

        if (! is_array($payload)) {
            return null;
        }

        return array_merge($payload, [
            'metadata' => [
                'bridge_event' => $event,
            ],
        ]);
    }
}
