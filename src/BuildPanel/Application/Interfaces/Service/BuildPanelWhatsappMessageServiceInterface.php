<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Service;

use App\Core\Domain\Entity\MessageEntity;

interface BuildPanelWhatsappMessageServiceInterface
{
    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function process(MessageEntity $message): array;
}
