<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces;

use App\Core\Application\DTO\SearchTravelItineraryInputDTO;
use App\Core\Application\DTO\SearchTravelItineraryOutputDTO;

interface SearchTravelItineraryAdapterInterface
{
    /**
     * @param  array{process?: string|null, municipality?: string|null, force?: string|null, region?: string|null, land_status?: string|null, landStatus?: string|null, progress?: string|null, requester?: string|null, term?: string|null}  $payload
     */
    public function fromArray(array $payload): SearchTravelItineraryInputDTO;

    /**
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    public function toArray(SearchTravelItineraryOutputDTO $dto): array;
}
