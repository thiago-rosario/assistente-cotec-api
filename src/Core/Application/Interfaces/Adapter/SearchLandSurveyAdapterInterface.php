<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Adapter;

use App\Core\Application\DTO\SearchLandSurveyInputDTO;
use App\Core\Application\DTO\SearchLandSurveyOutputDTO;

interface SearchLandSurveyAdapterInterface
{
    /**
     * @param  array{process?: string|null, municipality?: string|null, force?: string|null, region?: string|null, land_status?: string|null, landStatus?: string|null, progress?: string|null, term?: string|null}  $payload
     */
    public function fromArray(array $payload): SearchLandSurveyInputDTO;

    /**
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    public function toArray(SearchLandSurveyOutputDTO $dto): array;
}
