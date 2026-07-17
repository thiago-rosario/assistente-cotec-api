<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Interface\Usecase;

use App\Core\TravelReport\Application\DTO\ListTravelReportByMunicipalityIdInputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportsOutputDTO;

interface ListTravelReportByMunicipalityIdUsecaseInterface
{
    public function __invoke(ListTravelReportByMunicipalityIdInputDTO $dto): ListTravelReportsOutputDTO;
}
