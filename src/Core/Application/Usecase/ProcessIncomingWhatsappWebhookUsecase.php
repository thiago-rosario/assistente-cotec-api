<?php

declare(strict_types=1);

namespace App\Core\Application\Usecase;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageSenderInterface;
use App\Core\Application\Interfaces\Usecase\ProcessIncomingWhatsappWebhookUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Support\WhatsappLogContext;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProcessIncomingWhatsappWebhookUsecase implements ProcessIncomingWhatsappWebhookUsecaseInterface
{
    public function __construct(
        private readonly ProcessWhatsappMessageUsecaseInterface $processWhatsappMessage,
        private readonly WhatsappMessageSenderInterface $sender,
        private readonly ?WhatsappMessageResponseFormatterInterface $fallbackResponseFormatter = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function __invoke(ReceivedMessageInputDTO $input, ?int $attempt = null): array
    {
        $startedAt = microtime(true);

        Log::info('whatsapp_message_processing_started', [
            ...WhatsappLogContext::message($input->externalId, $input->phone, $input->source),
            'attempt' => $attempt,
        ]);

        $result = ($this->processWhatsappMessage)($input);
        $reply = (string) ($result['reply'] ?? '');
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        Log::info('whatsapp_message_processed', [
            ...WhatsappLogContext::message($input->externalId, $input->phone, $input->source),
            'intent' => $result['intent'] ?? null,
            'reply_length' => mb_strlen($reply),
            'duration_ms' => $durationMs,
            'attempt' => $attempt,
        ]);

        if (trim($reply) === '') {
            Log::error('whatsapp_message_empty_reply', [
                ...WhatsappLogContext::message($input->externalId, $input->phone, $input->source),
                'intent' => $result['intent'] ?? null,
                'duration_ms' => $durationMs,
                'attempt' => $attempt,
            ]);

            $fallback = $this->fallbackResponseFormatter?->error();

            if ($fallback === null || trim((string) ($fallback['reply'] ?? '')) === '') {
                return $result;
            }

            $result = $fallback;
            $reply = (string) $result['reply'];

            Log::warning('whatsapp_message_fallback_reply_used', [
                ...WhatsappLogContext::message($input->externalId, $input->phone, $input->source),
                'intent' => $result['intent'] ?? null,
                'reply_length' => mb_strlen($reply),
                'attempt' => $attempt,
            ]);
        }

        if ($input->phone === null || trim($input->phone) === '') {
            Log::warning('whatsapp_message_missing_phone', [
                ...WhatsappLogContext::message($input->externalId, $input->phone, $input->source),
                'intent' => $result['intent'] ?? null,
                'reply_length' => mb_strlen($reply),
                'duration_ms' => $durationMs,
                'attempt' => $attempt,
            ]);

            return $result;
        }

        $messageId = $input->externalId ?: Str::uuid()->toString();

        Log::info('whatsapp_reply_sending', [
            ...WhatsappLogContext::message($input->externalId, $input->phone, $input->source),
            'id_msg' => $messageId,
            'intent' => $result['intent'] ?? null,
            'reply_length' => mb_strlen($reply),
            'attempt' => $attempt,
        ]);

        try {
            $this->sender->send(
                phone: $input->phone,
                message: $reply,
                externalId: $messageId,
            );
        } catch (Throwable $throwable) {
            Log::error('whatsapp_reply_attempt_failed', [
                ...WhatsappLogContext::message($input->externalId, $input->phone, $input->source),
                'id_msg' => $messageId,
                'intent' => $result['intent'] ?? null,
                'reply_length' => mb_strlen($reply),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'http_status' => $this->httpStatus($throwable),
                'attempt' => $attempt,
                'exception' => $throwable::class,
                'exception_message' => $throwable->getMessage(),
                'exception_context' => $this->exceptionContext($throwable),
            ]);

            throw $throwable;
        }

        Log::info('whatsapp_reply_delivery_confirmed', [
            ...WhatsappLogContext::message($input->externalId, $input->phone, $input->source),
            'id_msg' => $messageId,
            'intent' => $result['intent'] ?? null,
            'reply_length' => mb_strlen($reply),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'attempt' => $attempt,
            'delivery_mode' => (string) config('whatsapp.message_sender', 'editacodigo'),
        ]);

        return $result;
    }

    private function httpStatus(Throwable $throwable): ?int
    {
        if (! method_exists($throwable, 'context')) {
            return null;
        }

        $context = $throwable->context();

        return is_array($context) ? ($context['status'] ?? null) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function exceptionContext(Throwable $throwable): ?array
    {
        if (! method_exists($throwable, 'context')) {
            return null;
        }

        $context = $throwable->context();

        return is_array($context) ? $context : null;
    }
}
