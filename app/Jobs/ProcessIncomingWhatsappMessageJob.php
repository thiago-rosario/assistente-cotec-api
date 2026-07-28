<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Usecase\ProcessIncomingWhatsappWebhookUsecaseInterface;
use App\Core\Application\Support\WhatsappLogContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessIncomingWhatsappMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly array $payload,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 5, 10];
    }

    public function handle(ProcessIncomingWhatsappWebhookUsecaseInterface $usecase): void
    {
        $usecase($this->input(), $this->attempts());
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    public function failed(?Throwable $exception): void
    {
        $input = $this->input();

        Log::critical('whatsapp_reply_permanently_failed', [
            ...WhatsappLogContext::message($input->externalId, $input->phone, $input->source),
            'attempt' => $this->attempts(),
            'exception' => $exception?->getMessage(),
            'exception_class' => $exception === null ? null : $exception::class,
            'exception_context' => $this->exceptionContext($exception),
        ]);
    }

    private function input(): ReceivedMessageInputDTO
    {
        return new ReceivedMessageInputDTO(
            message: (string) ($this->payload['message'] ?? ''),
            phone: $this->nullableString($this->payload['phone'] ?? null),
            senderName: $this->nullableString($this->payload['sender_name'] ?? null),
            receivedAt: $this->nullableString($this->payload['received_at'] ?? null),
            source: $this->nullableString($this->payload['source'] ?? null),
            externalId: $this->nullableString($this->payload['external_id'] ?? null),
            metadata: $this->metadata(),
        );
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null || ! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function metadata(): array
    {
        $metadata = $this->payload['metadata'] ?? [];

        return is_array($metadata) ? $metadata : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function exceptionContext(?Throwable $throwable): ?array
    {
        if ($throwable === null || ! method_exists($throwable, 'context')) {
            return null;
        }

        $context = $throwable->context();

        return is_array($context) ? $context : null;
    }
}
