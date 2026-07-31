<?php

declare(strict_types=1);

namespace App\Core\Application\Handler;

use App\BuildPanel\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Handler\WhatsappConversationFlowHandlerInterface;
use App\Core\Domain\Entity\MessageEntity;

class UnsupportedWhatsappMessageContentHandler implements WhatsappConversationFlowHandlerInterface
{
    public function __construct(
        private readonly WhatsappMessageResponseFormatterInterface $responseFormatter,
    ) {}

    public function supports(MessageEntity $message): bool
    {
        return ! $message->hasTextContent();
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function handle(MessageEntity $message): array
    {
        return $this->responseFormatter->unsupportedMessageContent();
    }
}
