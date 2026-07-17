<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Interface\Usecase;

use App\Core\TravelReport\Application\DTO\PersistTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;

interface PersistTravelReportUsecaseInterface
{
    public function __invoke(PersistTravelReportInputDTO $dto): PersistTravelReportOutputDTO;
}
