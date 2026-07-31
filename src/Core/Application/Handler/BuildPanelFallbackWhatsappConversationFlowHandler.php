<?php

declare(strict_types=1);

namespace App\Core\Application\Handler;

use App\BuildPanel\Application\Interfaces\Service\BuildPanelWhatsappMessageServiceInterface;
use App\Core\Application\Interfaces\Handler\WhatsappConversationFlowHandlerInterface;
use App\Core\Domain\Entity\MessageEntity;

class BuildPanelFallbackWhatsappConversationFlowHandler implements WhatsappConversationFlowHandlerInterface
{
    public function __construct(
        private readonly BuildPanelWhatsappMessageServiceInterface $buildPanelMessages,
    ) {}

    public function supports(MessageEntity $message): bool
    {
        return true;
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function handle(MessageEntity $message): array
    {
        return $this->buildPanelMessages->process($message);
    }
}
