<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Service;

interface ContractWhatsappMessageServiceInterface
{
    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function menu(): array;

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function searchPrompt(int $option): array;

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function search(int $option, string $searchTerm): array;
}
