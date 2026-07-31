<?php

declare(strict_types=1);

namespace App\Core\Application\Handler;

use App\Core\Application\Interfaces\Handler\WhatsappConversationFlowHandlerInterface;
use App\Core\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Application\Interfaces\Service\MessageIntentResolverInterface;
use App\Core\Application\Interfaces\Service\WhatsappMainMenuServiceInterface;
use App\Core\Domain\Entity\MessageEntity;

class MainMenuRequestWhatsappConversationFlowHandler implements WhatsappConversationFlowHandlerInterface
{
    public function __construct(
        private readonly MessageIntentResolverInterface $intentResolver,
        private readonly GreetingMessageMatcherServiceInterface $greetingMatcher,
        private readonly WhatsappMainMenuServiceInterface $mainMenu,
    ) {}

    public function supports(MessageEntity $message): bool
    {
        return $this->intentResolver->isMainMenuRequest($message)
            || $this->greetingMatcher->matches($message->content());
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function handle(MessageEntity $message): array
    {
        return $this->mainMenu->show($message);
    }
}
