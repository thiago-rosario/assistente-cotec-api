<?php

declare(strict_types=1);

namespace App\Core\Application\Trait;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Enum\WhatsappTerminalIntentEnum;

/**
 * Builds the responses and state transitions that complete a query.
 *
 * The composing use case supplies the response formatters and state store.
 */
trait BuildsWhatsappConversationResponseTrait
{
    /**
     * @param  array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}  $result
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    private function finalizeQuery(
        ReceivedMessageInputDTO $input,
        array $result,
    ): array {
        if (WhatsappTerminalIntentEnum::fromResponse($result) === null) {
            return $result;
        }

        $this->conversationState?->forget($input->phone);

        if ($this->coreResponseFormatter !== null) {
            $result['reply'] .= "\n\n".$this->coreResponseFormatter->queryCompleted()['reply'];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function isTerminalResponse(array $response): bool
    {
        return WhatsappTerminalIntentEnum::fromResponse($response) !== null;
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    private function mainMenu(?string $phone): array
    {
        $this->conversationState?->forget($phone);

        return $this->coreResponseFormatter->mainMenu();
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    private function closeConversation(?string $phone): array
    {
        $this->conversationState?->forget($phone);

        return $this->coreResponseFormatter?->conversationClosed()
            ?? $this->responseFormatter->conversationClosed();
    }

    private function hasConversationIntegration(): bool
    {
        return $this->coreResponseFormatter !== null
            && $this->conversationState !== null
            && $this->contract !== null
            && $this->municipalityExtractor !== null
            && $this->seiProcessRule !== null;
    }
}
