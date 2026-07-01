<?php

declare(strict_types=1);

namespace App\Core\Infra\Adapter;

use App\Core\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\Core\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\Core\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;

class WhatsappMessageSearchAdapter implements WhatsappMessageSearchAdapterInterface
{
    public function __construct(
        private readonly SearchTechnicalNotebookUsecaseInterface $searchTechnicalNotebook,
        private readonly SearchTechnicalNotebookAdapterInterface $technicalNotebookAdapter,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    public function search(string $intent, array $filters): array
    {
        return match ($intent) {
            'search_technical_notebook' => $this->technicalNotebookAdapter->toArray(
                ($this->searchTechnicalNotebook)($this->technicalNotebookAdapter->fromArray($filters)),
            ),
            default => $this->emptyResult(),
        };
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
