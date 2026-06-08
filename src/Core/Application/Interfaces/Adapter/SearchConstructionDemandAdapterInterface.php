<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Adapter;

use App\Core\Application\DTO\SearchConstructionDemandInputDTO;
use App\Core\Application\DTO\SearchConstructionDemandOutputDTO;

interface SearchConstructionDemandAdapterInterface
{
    /**
     * @param  array{process?: string|null, municipality?: string|null, force?: string|null, region?: string|null, land_status?: string|null, landStatus?: string|null, progress?: string|null, term?: string|null}  $payload
     */
    public function fromArray(array $payload): SearchConstructionDemandInputDTO;

    /**
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    public function toArray(SearchConstructionDemandOutputDTO $dto): array;
}
