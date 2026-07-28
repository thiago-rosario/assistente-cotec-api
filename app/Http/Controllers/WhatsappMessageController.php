<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Application\Interfaces\Adapter\WhatsappWebhookPayloadAdapterInterface;
use App\Core\Application\Interfaces\Usecase\AcceptIncomingWhatsappWebhookUsecaseInterface;
use App\Core\Exception\MessageNotContentException;
use App\Core\Exception\WhatsapppMessageException;
use App\Http\Helper\ResponseJsend;
use App\Http\Requests\WhatsappMessageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsappMessageController extends Controller
{
    public function __construct(
        private readonly AcceptIncomingWhatsappWebhookUsecaseInterface $usecase,
        private readonly WhatsappWebhookPayloadAdapterInterface $adapter,
    ) {}

    public function __invoke(WhatsappMessageRequest $request): JsonResponse
    {
        $payload = $request->validated();

        try {
            $input = $this->adapter->fromArray($payload);

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($result);

            return response()
                ->json($response->toArray(), 202);
        } catch (MessageNotContentException $e) {
            $reason = $this->firstString($payload, ['webhook_ignored_reason'])
                ?? $this->ignoredReason($payload);

            Log::info('whatsapp_webhook_ignored', [
                'reason' => $reason,
                'event_type' => $this->firstString($payload, ['webhook_event_type'])
                    ?? $this->eventType($payload),
                'external_id' => $this->firstString($payload, [
                    'id',
                    'message_id',
                    'messageId',
                    'external_id',
                    'externalId',
                    'id_mensagem',
                    'MessageSid',
                    'messages.*.id',
                    'statuses.*.id',
                    'entry.*.changes.*.value.messages.*.id',
                    'entry.*.changes.*.value.statuses.*.id',
                ]),
                'phone' => $this->firstString($payload, [
                    'phone',
                    'from',
                    'sender_phone',
                    'customer_contact',
                    'contact',
                    'telefone',
                    'messages.*.from',
                    'statuses.*.recipient_id',
                    'entry.*.changes.*.value.messages.*.from',
                    'entry.*.changes.*.value.statuses.*.recipient_id',
                    'entry.*.changes.*.value.contacts.*.wa_id',
                ]),
            ]);

            $response = new ResponseJsend([
                'accepted' => false,
                'ignored' => true,
                'reason' => $reason,
            ]);

            return response()
                ->json($response->toArray(), 200);
        } catch (WhatsapppMessageException $e) {
            $response = new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return response()
                ->json($response->toArray(), 500);
        } catch (\Throwable) {
            $response = new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: 'An unexpected error occurred',
                code: 500,
            );

            return response()
                ->json($response->toArray(), 500);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function ignoredReason(array $payload): string
    {
        $eventType = $this->eventType($payload);

        return match ($eventType) {
            'status' => 'delivery_status',
            'reaction' => 'reaction_without_reply',
            default => 'missing_message_content',
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function eventType(array $payload): string
    {
        $messageType = $this->firstString($payload, [
            'type',
            'messages.*.type',
            'entry.*.changes.*.value.messages.*.type',
        ]);

        if ($messageType === 'reaction') {
            return 'reaction';
        }

        if ($this->hasWildcardKey($payload, 'statuses.*.status')
            || $this->hasWildcardKey($payload, 'entry.*.changes.*.value.statuses.*.status')) {
            return 'status';
        }

        return $messageType ?? 'unknown';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $keys
     */
    private function firstString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = str_contains($key, '*')
                ? $this->firstWildcardValue($payload, $key)
                : Arr::get($payload, $key);

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
    private function hasWildcardKey(array $payload, string $key): bool
    {
        return $this->firstWildcardValue($payload, $key) !== null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function firstWildcardValue(array $payload, string $key): mixed
    {
        foreach (Arr::dot($payload) as $dottedKey => $value) {
            if (Str::is($key, (string) $dottedKey)) {
                return $value;
            }
        }

        return null;
    }
}
