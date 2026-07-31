<?php

declare(strict_types=1);

namespace App\Core\Application\Service;

use App\BuildPanel\Application\Interfaces\Service\BuildPanelWhatsappMessageServiceInterface;
use App\Core\Application\Interfaces\Service\MessageIntentResolverInterface;
use App\Core\Application\Interfaces\Service\WhatsappBuildPanelFlowServiceInterface;
use App\Core\Application\Interfaces\Service\WhatsappMainMenuServiceInterface;
use App\Core\Domain\Entity\MessageEntity;
use App\Core\Enum\WhatsappMenuOption;

class WhatsappBuildPanelFlowService implements WhatsappBuildPanelFlowServiceInterface
{
    public function __construct(
        private readonly WhatsappMainMenuServiceInterface $mainMenu,
        private readonly BuildPanelWhatsappMessageServiceInterface $buildPanelMessages,
        private readonly MessageIntentResolverInterface $intentResolver,
    ) {}

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function respondTo(MessageEntity $message): array
    {
        if ($this->intentResolver->menuOption($message) === WhatsappMenuOption::End) {
            return $this->mainMenu->endConversation($message);
        }

        if ($this->intentResolver->isMainMenuRequest($message)) {
            return $this->mainMenu->show($message);
        }

        return $this->buildPanelMessages->process($message);
    }
}
