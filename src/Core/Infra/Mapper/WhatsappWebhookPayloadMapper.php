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
     *     message: string|null,
     *     customer_contact: string|null,
     *     sender_name: string|null,
     *     received_at: string|null,
     *     source: string,
     *     external_id: string|null,
     *     document: array{
     *         original_file_name: string,
     *         mime_type: string,
     *         size_bytes: int,
     *         caption: string|null,
     *         content_base64: string|null,
     *         metadata: array<string, mixed>
     *     }|null,
     *     caption: string|null,
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
        $document = $this->document($payload);

        if ($message === null && $document === null) {
            throw new MessageNotContentException;
        }

        $caption = $document['caption'] ?? $this->firstString($payload, [
            'caption',
            'media.caption',
            'document.caption',
            'messages.*.caption',
            'entry.*.changes.*.value.messages.*.caption',
        ]);

        if ($document !== null) {
            $document['caption'] = $caption;
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
            'document' => $document,
            'caption' => $caption,
            'metadata' => $this->metadataWithoutDocumentContent($payload),
        ];
    }

    protected function defaultSource(): string
    {
        return 'whatsapp-webhook';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $keys
     */
    private function firstString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            foreach ($this->valuesForKey($payload, $key) as $value) {
                if (! is_scalar($value)) {
                    continue;
                }

                $value = trim((string) $value);

                if ($value !== '') {
                    return $value;
                }
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
            foreach ($this->valuesForKey($payload, $key) as $value) {
                if ($value === null || ! is_scalar($value)) {
                    continue;
                }

                $value = trim((string) $value);

                if ($value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<mixed>
     */
    private function valuesForKey(array $payload, string $key): array
    {
        $value = data_get($payload, $key);

        if (! str_contains($key, '*')) {
            return [$value];
        }

        if (! is_array($value)) {
            return [$value];
        }

        return Arr::flatten($value);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     original_file_name: string,
     *     mime_type: string,
     *     size_bytes: int,
     *     caption: string|null,
     *     content_base64: string|null,
     *     metadata: array<string, mixed>
     * }|null
     */
    private function document(array $payload): ?array
    {
        foreach ([
            'media',
            'document',
            'messages.*.document',
            'messages.*.media',
            'entry.*.changes.*.value.messages.*.document',
            'entry.*.changes.*.value.messages.*.media',
        ] as $key) {
            $value = data_get($payload, $key);

            foreach ($this->arrayValues($value) as $candidate) {
                if (! $this->isDocumentPayload($candidate, $key)) {
                    continue;
                }

                return [
                    'original_file_name' => $this->firstStringFromArray($candidate, [
                        'filename',
                        'file_name',
                        'original_filename',
                        'originalFileName',
                        'name',
                    ]) ?? '',
                    'mime_type' => $this->firstStringFromArray($candidate, [
                        'mimetype',
                        'mime_type',
                        'mimeType',
                    ]) ?? '',
                    'size_bytes' => $this->integerFromArray($candidate, [
                        'size',
                        'size_bytes',
                        'sizeBytes',
                    ]),
                    'caption' => $this->firstStringFromArray($candidate, ['caption']),
                    'content_base64' => $this->firstStringFromArray($candidate, [
                        'data',
                        'base64',
                        'content',
                        'content_base64',
                        'contentBase64',
                    ]),
                    'metadata' => $this->metadataWithoutDocumentContent($candidate, true),
                ];
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function arrayValues(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        if (! array_is_list($value)) {
            return [$value];
        }

        $values = [];

        foreach ($value as $item) {
            $values = [...$values, ...$this->arrayValues($item)];
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $document
     */
    private function isDocumentPayload(array $document, string $path): bool
    {
        $type = $this->firstStringFromArray($document, ['type', 'media_type', 'mediaType']);

        if ($type !== null && Str::lower($type) !== 'document') {
            return false;
        }

        return $type !== null
            || Str::endsWith($path, 'document')
            || $this->firstStringFromArray($document, [
                'filename',
                'file_name',
                'original_filename',
                'mimetype',
                'mime_type',
                'mimeType',
                'data',
                'base64',
                'content_base64',
                'contentBase64',
            ]) !== null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $keys
     */
    private function firstStringFromArray(array $payload, array $keys): ?string
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
    private function integerFromArray(array $payload, array $keys): int
    {
        foreach ($keys as $key) {
            $value = Arr::get($payload, $key);

            if (is_int($value)) {
                return $value;
            }

            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return 0;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function metadataWithoutDocumentContent(array $payload, bool $insideDocument = false): array
    {
        $metadata = [];

        foreach ($payload as $key => $value) {
            $key = (string) $key;
            $documentContext = $insideDocument || in_array($key, ['media', 'document'], true);

            if ($documentContext && in_array($key, [
                'data',
                'base64',
                'content',
                'content_base64',
                'contentBase64',
            ], true)) {
                continue;
            }

            $metadata[$key] = is_array($value)
                ? $this->metadataWithoutDocumentContent($value, $documentContext)
                : $value;
        }

        return $metadata;
    }
}
