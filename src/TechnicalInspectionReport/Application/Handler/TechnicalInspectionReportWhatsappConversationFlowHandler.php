<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Handler;

use App\Core\Application\Interfaces\Handler\WhatsappConversationFlowHandlerInterface;
use App\Core\Domain\Entity\MessageEntity;
use App\Core\Domain\Repository\WhatsappConversationStateRepositoryInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Service\TechnicalInspectionReportWhatsappConversationFlowServiceInterface;

final class TechnicalInspectionReportWhatsappConversationFlowHandler implements WhatsappConversationFlowHandlerInterface
{
    public function __construct(
        private readonly WhatsappConversationStateRepositoryInterface $conversationStates,
        private readonly TechnicalInspectionReportWhatsappConversationFlowServiceInterface $flow,
    ) {}

    public function supports(MessageEntity $message): bool
    {
        return $this->conversationStates->get($message)?->isTechnicalInspectionReport() ?? false;
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function handle(MessageEntity $message): array
    {
        return $this->flow->respondTo($message);
    }
}
