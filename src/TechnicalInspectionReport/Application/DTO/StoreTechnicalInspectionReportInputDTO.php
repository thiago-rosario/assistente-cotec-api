<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\DTO;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;

readonly class StoreTechnicalInspectionReportInputDTO
{
    public function __construct(
        public TechnicalInspectionReportEntity $report,
        public string $documentPath,
    ) {}
}
