<?php

declare(strict_types=1);

namespace App\Core\Infra\Adapter;

use App\Core\Application\DTO\SearchLandSurveyInputDTO;
use App\Core\Application\DTO\SearchLandSurveyOutputDTO;
use App\Core\Application\Interfaces\SearchLandSurveyAdapterInterface;

class SearchLandSurveyAdapter implements SearchLandSurveyAdapterInterface
{
    public function fromArray(array $payload): SearchLandSurveyInputDTO
    {
        return new SearchLandSurveyInputDTO(
            process: $payload['process'] ?? null,
            municipality: $payload['municipality'] ?? null,
            force: $payload['force'] ?? null,
            region: $payload['region'] ?? null,
            landStatus: $payload['land_status'] ?? $payload['landStatus'] ?? null,
            progress: $payload['progress'] ?? null,
            term: $payload['term'] ?? null,
        );
    }

    public function toArray(SearchLandSurveyOutputDTO $dto): array
    {
        return [
            'term' => $dto->term,
            'total' => $dto->total,
            'data' => $dto->data,
        ];
    }
}
