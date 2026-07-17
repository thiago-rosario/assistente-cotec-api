<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Interface\Usecase;

use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessInputDTO;
use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessOutputDTO;

interface FindTravelReportBySeiProcessUsecaseInterface
{
    public function __invoke(FindTravelReportBySeiProcessInputDTO $dto): FindTravelReportBySeiProcessOutputDTO;
}
