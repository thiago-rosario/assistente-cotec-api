<?php

declare(strict_types=1);

namespace App\Core\Conversation\Infra\Mapper;

use App\Core\Conversation\Application\Interfaces\Mapper\PythonMessagePayloadMapperInterface;
use App\Core\Conversation\Exception\MessageNotContentException;
use Illuminate\Support\Arr;

class PythonMessagePayloadMapper implements PythonMessagePayloadMapperInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     message: string,
     *     customer_contact: string|null,
     *     sender_name: string|null,
     *     received_at: string|null,
     *     source: string,
     *     external_id: string|null,
     *     metadata: array<string, mixed>
     * }
     */
    public function map(array $payload): array
    {
        $message = $this->firstPresentString($payload, [
            'message',
            'body',
            'content',
            'text',
            'last_message.content',
            'whatsapp_message.content',
        ]);

        if ($message === null) {
            throw new MessageNotContentException;
        }

        return [
            'message' => $message,
            'customer_contact' => $this->firstString($payload, [
                'phone',
                'from',
                'sender_phone',
                'customer_contact',
                'contact',
                'last_message.customer_contact',
                'whatsapp_message.customer_contact',
            ]),
            'sender_name' => $this->firstString($payload, [
                'sender_name',
                'senderName',
                'name',
                'customer_name',
                'contact_name',
            ]),
            'received_at' => $this->firstString($payload, [
                'received_at',
                'receivedAt',
                'timestamp',
                'created_at',
            ]),
            'source' => $this->firstString($payload, [
                'source',
                'channel',
                'provider',
            ]) ?? 'python-whatsapp',
            'external_id' => $this->firstString($payload, [
                'id',
                'message_id',
                'messageId',
                'external_id',
                'externalId',
                'MessageSid',
            ]),
            'metadata' => $payload,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $keys
     */
    private function firstString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($payload, $key);

            if (! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $keys
     */
    private function firstPresentString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! Arr::has($payload, $key)) {
                continue;
            }

            $value = Arr::get($payload, $key);

            if (! is_scalar($value)) {
                continue;
            }

            return trim((string) $value);
        }

        return null;
    }
}
