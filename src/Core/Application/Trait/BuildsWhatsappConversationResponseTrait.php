<?php

declare(strict_types=1);

namespace App\Core\Application\Trait;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\DTO\WhatsappConversationStateDTO;

/**
 * Builds the responses and state transitions that close or complete a query.
 *
 * The composing use case supplies the response formatters and state store.
 */
trait BuildsWhatsappConversationResponseTrait
{
    /**
     * @param  array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}  $result
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    private function finishQuery(
        ReceivedMessageInputDTO $input,
        array $result,
        ?int $contractOption = null,
    ): array {
        if (! $this->isCompletedQuery($result)) {
            $this->conversationState->forget($input->phone);

            return $result;
        }

        $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
            route: self::PostQueryActionRoute,
            contractOption: $contractOption,
        ));

        $result['reply'] .= "\n\n".$this->coreResponseFormatter->postQueryAction()['reply'];

        return $result;
    }

    private function isCompletedQuery(array $result): bool
    {
        return in_array($result['intent'] ?? null, [
            'search_technical_notebook',
            'contract_value_additives',
            'contract_adjustments',
            'contract_execution_deadlines',
            'contract_summary',
        ], true);
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
