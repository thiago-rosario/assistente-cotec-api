<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Adapter;

interface WhatsappMessageSearchAdapterInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    public function search(string $intent, array $filters): array;
}
