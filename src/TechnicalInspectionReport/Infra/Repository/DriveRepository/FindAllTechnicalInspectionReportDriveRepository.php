<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\DriveRepository;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;

class FindAllTechnicalInspectionReportDriveRepository
{
    public function __construct(
        private readonly TechnicalInspectionReportDriveRecordRepository $records,
    ) {}

    /**
     * @return list<TechnicalInspectionReportEntity>
     */
    public function findAll(): array
    {
        return $this->records->findReports();
    }
}
