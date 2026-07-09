<?php

declare(strict_types=1);

namespace App\Core\Infra\Adapter;

use App\Core\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageSearchHandlerInterface;

class WhatsappMessageSearchAdapter implements WhatsappMessageSearchAdapterInterface
{
    /**
     * @param  iterable<WhatsappMessageSearchHandlerInterface>  $handlers
     */
    public function __construct(
        private readonly iterable $handlers,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    public function search(string $intent, array $filters): array
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($intent)) {
                return $handler->search($filters);
            }
        }

        return $this->emptyResult();
    }

    /**
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    private function emptyResult(): array
    {
        return [
            'term' => null,
            'total' => 0,
            'data' => [],
        ];
    }
}
