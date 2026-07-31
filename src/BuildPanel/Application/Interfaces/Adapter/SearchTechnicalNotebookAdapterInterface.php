<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Adapter;

use App\BuildPanel\Application\DTO\SearchTechnicalNotebookInputDTO;
use App\BuildPanel\Application\DTO\SearchTechnicalNotebookOutputDTO;

interface SearchTechnicalNotebookAdapterInterface
{
    /**
     * @param  array{process?: string|null, municipality?: string|null, force?: string|null, build_status?: string|null, buildStatus?: string|null, term?: string|null}  $payload
     */
    public function fromArray(array $payload): SearchTechnicalNotebookInputDTO;

    /**
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    public function toArray(SearchTechnicalNotebookOutputDTO $dto): array;
}
