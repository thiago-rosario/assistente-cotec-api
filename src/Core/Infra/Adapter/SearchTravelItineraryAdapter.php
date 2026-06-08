<?php

declare(strict_types=1);

namespace App\Core\Infra\Adapter;

use App\Core\Application\DTO\SearchTravelItineraryInputDTO;
use App\Core\Application\DTO\SearchTravelItineraryOutputDTO;
use App\Core\Application\Interfaces\Adapter\SearchTravelItineraryAdapterInterface;

class SearchTravelItineraryAdapter implements SearchTravelItineraryAdapterInterface
{
    public function fromArray(array $payload): SearchTravelItineraryInputDTO
    {
        return new SearchTravelItineraryInputDTO(
            process: $payload['process'] ?? null,
            municipality: $payload['municipality'] ?? null,
            force: $payload['force'] ?? null,
            region: $payload['region'] ?? null,
            landStatus: $payload['land_status'] ?? $payload['landStatus'] ?? null,
            progress: $payload['progress'] ?? null,
            requester: $payload['requester'] ?? null,
            term: $payload['term'] ?? null,
        );
    }

    public function toArray(SearchTravelItineraryOutputDTO $dto): array
    {
        return [
            'term' => $dto->term,
            'total' => $dto->total,
            'data' => $dto->data,
        ];
    }
}
