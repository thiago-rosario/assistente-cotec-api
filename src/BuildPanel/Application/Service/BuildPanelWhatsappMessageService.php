<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Service;

use App\BuildPanel\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\BuildPanel\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\BuildPanelWhatsappMessageServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\BuildPanel\Enum\WhatsappMessageIntentEnum;
use App\Core\Domain\Entity\MessageEntity;
use App\Core\Domain\Repository\WhatsappConversationStateRepositoryInterface;
use App\Core\Enum\WhatsappConversationState;

class BuildPanelWhatsappMessageService implements BuildPanelWhatsappMessageServiceInterface
{
    public function __construct(
        private readonly ResolveWhatsappMessageInterpretationServiceInterface $resolveInterpretation,
        private readonly WhatsappMessageSearchAdapterInterface $searchAdapter,
        private readonly WhatsappMessageResponseFormatterInterface $responseFormatter,
        private readonly AcceptedWhatsappMessageInterpretationServiceInterface $acceptedInterpretation,
        private readonly WhatsappConversationStateRepositoryInterface $conversationStates,
    ) {}

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function process(MessageEntity $message): array
    {
        $interpretation = ($this->resolveInterpretation)($message->content());

        if ($interpretation->intent === WhatsappMessageIntentEnum::UNKNOWN->value) {
            return $this->unknownResponse($message);
        }

        if (! $this->acceptedInterpretation->accepts($interpretation->intent, $interpretation->filters)) {
            return $this->unknownResponse($message);
        }

        $result = $this->searchAdapter->search(
            $interpretation->intent,
            $interpretation->filters,
        );

        $response = $this->responseFormatter->format(
            $interpretation->intent,
            $interpretation->filters,
            $result,
        );

        $this->clearConversation($message);

        return $response;
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    private function unknownResponse(MessageEntity $message): array
    {
        if ($this->conversationStates->get($message) === WhatsappConversationState::BuildPanel) {
            return $this->responseFormatter->unknownIntent();
        }

        return $this->responseFormatter->globalUnknownIntent();
    }

    private function clearConversation(MessageEntity $message): void
    {
        $this->conversationStates->forgetMunicipality($message);
        $this->conversationStates->forget($message);
    }
}
