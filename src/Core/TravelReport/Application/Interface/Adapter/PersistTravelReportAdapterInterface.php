<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Interface\Adapter;

use App\Core\TravelReport\Application\DTO\PersistTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;

interface PersistTravelReportAdapterInterface
{
    public function toInput(array $payload): PersistTravelReportInputDTO;

    public function toArray(PersistTravelReportOutputDTO $dto): array;
}
