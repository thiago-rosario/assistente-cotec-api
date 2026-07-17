<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Interface\Usecase;

use App\Core\TravelReport\Application\DTO\DeleteTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\DeleteTravelReportOutputDTO;

interface DeleteTravelReportUsecaseInterface
{
    public function __invoke(DeleteTravelReportInputDTO $dto): DeleteTravelReportOutputDTO;
}
