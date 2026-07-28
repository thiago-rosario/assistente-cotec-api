<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Helper\ResponseJsend;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsappMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        Log::info('whatsapp_webhook_payload_received', [
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'content_type' => $this->headers->get('content-type'),
            'content_length' => $this->headers->get('content-length'),
            'raw_body' => $this->getContent(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        $data = $this->all();

        foreach (['telefone', 'timestamp', 'id_mensagem'] as $key) {
            if (Arr::has($data, $key)) {
                $data[$key] = $this->nullableScalarString(Arr::get($data, $key));
            }
        }

        $this->fillMissingString($data, 'phone', [
            'messages.*.from',
            'statuses.*.recipient_id',
            'entry.*.changes.*.value.messages.*.from',
            'entry.*.changes.*.value.statuses.*.recipient_id',
            'entry.*.changes.*.value.contacts.*.wa_id',
        ]);
        $this->fillMissingString($data, 'sender_name', [
            'contacts.*.profile.name',
            'entry.*.changes.*.value.contacts.*.profile.name',
        ]);
        $this->fillMissingString($data, 'received_at', [
            'messages.*.timestamp',
            'statuses.*.timestamp',
            'entry.*.changes.*.value.messages.*.timestamp',
            'entry.*.changes.*.value.statuses.*.timestamp',
        ]);
        $this->fillMissingString($data, 'source', [
            'object',
            'entry.*.changes.*.value.messaging_product',
        ]);
        $this->fillMissingString($data, 'external_id', [
            'messages.*.id',
            'statuses.*.id',
            'entry.*.changes.*.value.messages.*.id',
            'entry.*.changes.*.value.statuses.*.id',
        ]);

        return [
            ...$data,
            'message' => $this->firstMessageValue($data),
            'webhook_event_type' => $this->eventType($data),
            'webhook_ignored_reason' => $this->ignoredReason($data),
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'text' => ['nullable'],
            'text.body' => ['nullable', 'string'],
            'texto' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'from' => ['nullable', 'string'],
            'sender_phone' => ['nullable', 'string'],
            'customer_contact' => ['nullable', 'string'],
            'contact' => ['nullable', 'string'],
            'telefone' => ['nullable', 'string'],
            'sender_name' => ['nullable', 'string'],
            'senderName' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'customer_name' => ['nullable', 'string'],
            'contact_name' => ['nullable', 'string'],
            'received_at' => ['nullable', 'string'],
            'receivedAt' => ['nullable', 'string'],
            'timestamp' => ['nullable', 'string'],
            'created_at' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
            'channel' => ['nullable', 'string'],
            'provider' => ['nullable', 'string'],
            'id' => ['nullable', 'string'],
            'message_id' => ['nullable', 'string'],
            'messageId' => ['nullable', 'string'],
            'external_id' => ['nullable', 'string'],
            'externalId' => ['nullable', 'string'],
            'id_mensagem' => ['nullable', 'string'],
            'MessageSid' => ['nullable', 'string'],
            'last_message' => ['nullable', 'array'],
            'last_message.content' => ['nullable', 'string'],
            'last_message.customer_contact' => ['nullable', 'string'],
            'whatsapp_message' => ['nullable', 'array'],
            'whatsapp_message.content' => ['nullable', 'string'],
            'whatsapp_message.customer_contact' => ['nullable', 'string'],
            'object' => ['nullable', 'string'],
            'messages' => ['nullable', 'array'],
            'messages.*' => ['nullable', 'array'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['nullable', 'array'],
            'entry' => ['nullable', 'array'],
            'entry.*' => ['nullable', 'array'],
            'entry.*.changes' => ['nullable', 'array'],
            'entry.*.changes.*' => ['nullable', 'array'],
            'entry.*.changes.*.field' => ['nullable', 'string'],
            'entry.*.changes.*.value' => ['nullable', 'array'],
            'webhook_event_type' => ['nullable', 'string'],
            'webhook_ignored_reason' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::warning('whatsapp_webhook_validation_failed', [
            'errors' => $validator->errors()->toArray(),
            'raw_body' => $this->getContent(),
        ]);

        $response = new ResponseJsend(
            data: $validator->errors()->toArray(),
            status: ResponseJsend::STATUS_FAIL,
        );

        throw new HttpResponseException(
            response()->json($response->toArray(), 422),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function firstMessageValue(array $data): mixed
    {
        foreach ([
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
        ] as $key) {
            $value = $this->stringValueForKey($data, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function nullableScalarString(mixed $value): mixed
    {
        if ($value === null || ! is_scalar($value)) {
            return $value;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function stringValueForKey(array $data, string $key): ?string
    {
        if (str_contains($key, '*')) {
            foreach (Arr::dot($data) as $dottedKey => $value) {
                if (! Str::is($key, (string) $dottedKey) || ! is_scalar($value)) {
                    continue;
                }

                return trim((string) $value);
            }

            return null;
        }

        $value = Arr::get($data, $key);

        if (! is_scalar($value)) {
            return null;
        }

        return trim((string) $value);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $keys
     */
    private function fillMissingString(array &$data, string $target, array $keys): void
    {
        $current = $this->stringValueForKey($data, $target);

        if ($current !== null && $current !== '') {
            return;
        }

        foreach ($keys as $key) {
            $value = $this->stringValueForKey($data, $key);

            if ($value !== null && $value !== '') {
                $data[$target] = $value;

                return;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ignoredReason(array $data): string
    {
        return match ($this->eventType($data)) {
            'status' => 'delivery_status',
            'reaction' => 'reaction_without_reply',
            default => 'missing_message_content',
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function eventType(array $data): string
    {
        $messageType = $this->firstString($data, [
            'type',
            'messages.*.type',
            'entry.*.changes.*.value.messages.*.type',
        ]);

        if ($messageType === 'reaction') {
            return 'reaction';
        }

        if ($this->stringValueForKey($data, 'statuses.*.status') !== null
            || $this->stringValueForKey($data, 'entry.*.changes.*.value.statuses.*.status') !== null) {
            return 'status';
        }

        return $messageType ?? 'unknown';
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $keys
     */
    private function firstString(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->stringValueForKey($data, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
