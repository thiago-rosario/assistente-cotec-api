<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\DriveRepository;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;

class SaveTechnicalInspectionReportDriveRepository
{
    public function __construct(
        private readonly TechnicalInspectionReportDriveRecordRepository $records,
    ) {}

    public function save(TechnicalInspectionReportEntity $report): void
    {
        $this->records->save($report);
    }
}
