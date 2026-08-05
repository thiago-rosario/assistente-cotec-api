<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\DTO;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;

readonly class RegisterTechnicalInspectionReportCatalogInputDTO
{
    public function __construct(
        public TechnicalInspectionReportGoogleSheetEntity $sheet,
    ) {}
}
