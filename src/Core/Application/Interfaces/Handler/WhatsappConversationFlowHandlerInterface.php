<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Handler;

use App\Core\Domain\Entity\MessageEntity;

interface WhatsappConversationFlowHandlerInterface
{
    public function supports(MessageEntity $message): bool;

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function handle(MessageEntity $message): array;
}
