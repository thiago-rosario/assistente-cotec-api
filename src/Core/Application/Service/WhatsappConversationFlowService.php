<?php

declare(strict_types=1);

namespace App\Core\Application\Service;

use App\Core\Application\Interfaces\Handler\WhatsappConversationFlowHandlerInterface;
use App\Core\Application\Interfaces\Service\WhatsappConversationFlowServiceInterface;
use App\Core\Domain\Entity\MessageEntity;
use RuntimeException;

class WhatsappConversationFlowService implements WhatsappConversationFlowServiceInterface
{
    /**
     * @param  iterable<WhatsappConversationFlowHandlerInterface>  $handlers
     */
    public function __construct(
        private readonly iterable $handlers,
    ) {}

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function respondTo(MessageEntity $message): array
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($message)) {
                return $handler->handle($message);
            }
        }

        throw new RuntimeException('No WhatsApp conversation flow handler matched the message.');
    }
}
