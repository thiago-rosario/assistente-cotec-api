<?php

declare(strict_types=1);

namespace App\Core\Infra\Adapter;

use App\Core\Application\DTO\SearchTechnicalNotebookInputDTO;
use App\Core\Application\DTO\SearchTechnicalNotebookOutputDTO;
use App\Core\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;

class SearchTechnicalNotebookAdapter implements SearchTechnicalNotebookAdapterInterface
{
    public function fromArray(array $payload): SearchTechnicalNotebookInputDTO
    {
        return new SearchTechnicalNotebookInputDTO(
            process: $payload['process'] ?? null,
            municipality: $payload['municipality'] ?? null,
            force: $payload['force'] ?? null,
            buildStatus: $payload['build_status'] ?? $payload['buildStatus'] ?? null,
            term: $payload['term'] ?? null,
        );
    }

    public function toArray(SearchTechnicalNotebookOutputDTO $dto): array
    {
        return [
            'term' => $dto->term,
            'total' => $dto->total,
            'data' => $dto->data,
        ];
    }
}
