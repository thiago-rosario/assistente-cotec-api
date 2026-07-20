<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Interface\Adapter;

use App\Core\TravelReport\Application\DTO\ListTravelReportByMunicipalityIdInputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportsOutputDTO;

interface ListTravelReportByMunicipalityIdAdapterInterface
{
    /**
     * @param  array{municipality_id: int|string}  $payload
     */
    public function toInput(array $payload): ListTravelReportByMunicipalityIdInputDTO;

    /**
     * @return array<string, mixed>
     */
    public function toArray(ListTravelReportsOutputDTO $dto): array;
}
