<?php

declare(strict_types=1);

namespace App\Core\Infra\Adapter;

use App\Core\Application\DTO\SearchConstructionDemandInputDTO;
use App\Core\Application\DTO\SearchConstructionDemandOutputDTO;
use App\Core\Application\Interfaces\SearchConstructionDemandAdapterInterface;

class SearchConstructionDemandAdapter implements SearchConstructionDemandAdapterInterface
{
    public function fromArray(array $payload): SearchConstructionDemandInputDTO
    {
        return new SearchConstructionDemandInputDTO(
            process: $payload['process'] ?? null,
            municipality: $payload['municipality'] ?? null,
            force: $payload['force'] ?? null,
            region: $payload['region'] ?? null,
            landStatus: $payload['land_status'] ?? $payload['landStatus'] ?? null,
            progress: $payload['progress'] ?? null,
            term: $payload['term'] ?? null,
        );
    }

    public function toArray(SearchConstructionDemandOutputDTO $dto): array
    {
        return [
            'term' => $dto->term,
            'total' => $dto->total,
            'data' => $dto->data,
        ];
    }
}
