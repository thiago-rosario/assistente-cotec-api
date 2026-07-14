<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Infra\Adapter;

use App\Core\BuildPanel\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\Core\BuildPanel\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;
use App\Core\Conversation\Application\Interfaces\Service\WhatsappMessageSearchHandlerInterface;
use App\Core\Conversation\Enum\WhatsappMessageIntentEnum;

class TechnicalNotebookWhatsappSearchHandler implements WhatsappMessageSearchHandlerInterface
{
    public function __construct(
        private readonly SearchTechnicalNotebookUsecaseInterface $searchTechnicalNotebook,
        private readonly SearchTechnicalNotebookAdapterInterface $technicalNotebookAdapter,
    ) {}

    public function supports(string $intent): bool
    {
        return $intent === WhatsappMessageIntentEnum::SEARCH_TECHNICAL_NOTEBOOK->value;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    public function search(array $filters): array
    {
        return $this->technicalNotebookAdapter->toArray(
            ($this->searchTechnicalNotebook)($this->technicalNotebookAdapter->fromArray($filters)),
        );
    }
}
