<?php

declare(strict_types=1);

namespace App\Core\Application\Handler;

use App\Core\Application\Interfaces\Handler\WhatsappConversationFlowHandlerInterface;
use App\Core\Application\Interfaces\Service\MessageIntentResolverInterface;
use App\Core\Application\Interfaces\Service\WhatsappMainMenuServiceInterface;
use App\Core\Domain\Entity\MessageEntity;
use RuntimeException;

class MainMenuOptionWhatsappConversationFlowHandler implements WhatsappConversationFlowHandlerInterface
{
    public function __construct(
        private readonly MessageIntentResolverInterface $intentResolver,
        private readonly WhatsappMainMenuServiceInterface $mainMenu,
    ) {}

    public function supports(MessageEntity $message): bool
    {
        return $this->intentResolver->menuOption($message) !== null;
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function handle(MessageEntity $message): array
    {
        $menuOption = $this->intentResolver->menuOption($message);

        if ($menuOption === null) {
            throw new RuntimeException('Main menu option handler received a message without menu option.');
        }

        return $this->mainMenu->handleOption($message, $menuOption);
    }
}
