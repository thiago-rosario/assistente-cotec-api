<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Service;

use App\Core\Domain\Entity\MessageEntity;

interface WhatsappConversationFlowServiceInterface
{
    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function respondTo(MessageEntity $message): array;
}
