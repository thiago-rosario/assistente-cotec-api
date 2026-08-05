<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\DTO;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;

readonly class StoreTechnicalInspectionReportOutputDTO
{
    public function __construct(
        public TechnicalInspectionReportEntity $report,
        public StoredTechnicalInspectionReportFileDTO $storedFile,
        public TechnicalInspectionReportGoogleSheetEntity $catalogEntry,
    ) {}
}
