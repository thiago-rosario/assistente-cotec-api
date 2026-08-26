<?php

declare(strict_types=1);

namespace App\Core\Infra\Mapper;

use App\Core\Application\Interfaces\Mapper\WhatsappWebhookPayloadMapperInterface;
use App\Core\Exception\MessageNotContentException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class WhatsappWebhookPayloadMapper implements WhatsappWebhookPayloadMapperInterface
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
            'texto',
            'body',
            'content',
            'text.body',
            'text',
            'last_message.content',
            'whatsapp_message.content',
            'messages.*.text.body',
            'messages.*.button.text',
            'messages.*.interactive.button_reply.title',
            'messages.*.interactive.list_reply.title',
            'entry.*.changes.*.value.messages.*.text.body',
            'entry.*.changes.*.value.messages.*.button.text',
            'entry.*.changes.*.value.messages.*.interactive.button_reply.title',
            'entry.*.changes.*.value.messages.*.interactive.list_reply.title',
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
                'telefone',
                'last_message.customer_contact',
                'whatsapp_message.customer_contact',
                'messages.*.from',
                'entry.*.changes.*.value.messages.*.from',
                'entry.*.changes.*.value.contacts.*.wa_id',
            ]),
            'sender_name' => $this->firstString($payload, [
                'sender_name',
                'senderName',
                'name',
                'customer_name',
                'contact_name',
                'contacts.*.profile.name',
                'entry.*.changes.*.value.contacts.*.profile.name',
            ]),
            'received_at' => $this->firstString($payload, [
                'received_at',
                'receivedAt',
                'timestamp',
                'created_at',
                'messages.*.timestamp',
                'entry.*.changes.*.value.messages.*.timestamp',
            ]),
            'source' => $this->firstString($payload, [
                'source',
                'channel',
                'provider',
                'object',
                'entry.*.changes.*.value.messaging_product',
            ]) ?? $this->defaultSource(),
            'external_id' => $this->firstString($payload, [
                'id',
                'message_id',
                'messageId',
                'external_id',
                'externalId',
                'id_mensagem',
                'MessageSid',
                'messages.*.id',
                'entry.*.changes.*.value.messages.*.id',
            ]),
            'metadata' => $this->metadata($payload),
        ];
    }

    protected function defaultSource(): string
    {
        return 'whatsapp-webhook';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, string>
     */
    private function metadata(array $payload): array
    {
        $metadata = [];

        foreach (['source', 'channel', 'provider', 'object', 'type'] as $key) {
            $value = Arr::get($payload, $key);

            if (! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                $metadata[$key] = $value;
            }
        }

        return $metadata;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $keys
     */
    private function firstString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->valueForKey($payload, $key);

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
            $value = $this->valueForKey($payload, $key);

            if ($value === null) {
                continue;
            }

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
     */
    private function valueForKey(array $payload, string $key): mixed
    {
        if (! str_contains($key, '*')) {
            return Arr::get($payload, $key);
        }

        foreach (Arr::dot($payload) as $dottedKey => $value) {
            if (Str::is($key, (string) $dottedKey)) {
                return $value;
            }
        }

        return null;
    }
}
