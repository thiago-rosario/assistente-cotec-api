<?php

declare(strict_types=1);

namespace App\Core\Application\Trait;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Support\WhatsappLogContext;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Records processing failures and prevents state cleanup from hiding them.
 */
trait HandlesWhatsappProcessingErrorsTrait
{
    private function clearConversationState(?string $phone): void
    {
        try {
            $this->conversationState?->forget($phone);
        } catch (Throwable $throwable) {
            Log::error('whatsapp_conversation_state_clear_failed', [
                ...WhatsappLogContext::message(null, $phone, null),
                'exception' => $throwable::class,
                'exception_message' => $throwable->getMessage(),
                'exception_context' => $this->exceptionContext($throwable),
            ]);
        }
    }

    private function logProcessingException(
        string $event,
        ReceivedMessageInputDTO $input,
        Throwable $throwable,
        string $level,
    ): void {
        Log::log($level, $event, [
            ...WhatsappLogContext::message($input->externalId, $input->phone, $input->source),
            'exception' => $throwable::class,
            'exception_message' => $throwable->getMessage(),
            'exception_context' => $this->exceptionContext($throwable),
        ]);
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
