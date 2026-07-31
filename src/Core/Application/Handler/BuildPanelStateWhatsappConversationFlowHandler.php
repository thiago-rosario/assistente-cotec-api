<?php

declare(strict_types=1);

namespace App\Core\Application\Handler;

use App\Core\Application\Interfaces\Handler\WhatsappConversationFlowHandlerInterface;
use App\Core\Application\Interfaces\Service\WhatsappBuildPanelFlowServiceInterface;
use App\Core\Domain\Entity\MessageEntity;
use App\Core\Domain\Repository\WhatsappConversationStateRepositoryInterface;
use App\Core\Enum\WhatsappConversationState;

class BuildPanelStateWhatsappConversationFlowHandler implements WhatsappConversationFlowHandlerInterface
{
    public function __construct(
        private readonly WhatsappConversationStateRepositoryInterface $conversationStates,
        private readonly WhatsappBuildPanelFlowServiceInterface $buildPanelFlow,
    ) {}

    public function supports(MessageEntity $message): bool
    {
        return $this->conversationStates->get($message) === WhatsappConversationState::BuildPanel;
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function handle(MessageEntity $message): array
    {
        return $this->buildPanelFlow->respondTo($message);
    }
}
