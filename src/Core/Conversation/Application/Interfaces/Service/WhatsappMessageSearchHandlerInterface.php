<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Service;

interface WhatsappMessageSearchHandlerInterface
{
    public function supports(string $intent): bool;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    public function search(array $filters): array;
}
