<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Helper\ResponseJsend;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;

class WhatsappMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return [
            ...$this->all(),
            'message' => $this->firstMessageValue(),
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string'],
            'phone' => ['nullable', 'string'],
            'from' => ['nullable', 'string'],
            'sender_phone' => ['nullable', 'string'],
            'customer_contact' => ['nullable', 'string'],
            'contact' => ['nullable', 'string'],
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
            'MessageSid' => ['nullable', 'string'],
            'last_message' => ['nullable', 'array'],
            'last_message.content' => ['nullable', 'string'],
            'last_message.customer_contact' => ['nullable', 'string'],
            'whatsapp_message' => ['nullable', 'array'],
            'whatsapp_message.content' => ['nullable', 'string'],
            'whatsapp_message.customer_contact' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $response = new ResponseJsend(
            data: $validator->errors()->toArray(),
            status: ResponseJsend::STATUS_FAIL,
        );

        throw new HttpResponseException(
            response()->json($response->toArray(), 422),
        );
    }

    private function firstMessageValue(): mixed
    {
        foreach (['message', 'body', 'content', 'text', 'last_message.content', 'whatsapp_message.content'] as $key) {
            $value = Arr::get($this->all(), $key);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }
}
