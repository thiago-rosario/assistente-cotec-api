<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Interfaces\Usecase;

use App\TechnicalInspectionReport\Application\DTO\StoreTechnicalInspectionReportInputDTO;
use App\TechnicalInspectionReport\Application\DTO\StoreTechnicalInspectionReportOutputDTO;

interface StoreTechnicalInspectionReportUsecaseInterface
{
    public function __invoke(StoreTechnicalInspectionReportInputDTO $input): StoreTechnicalInspectionReportOutputDTO;
}
