<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Service;

interface BuildPanelWhatsappMessageServiceInterface
{
    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function process(string $message): array;
}
