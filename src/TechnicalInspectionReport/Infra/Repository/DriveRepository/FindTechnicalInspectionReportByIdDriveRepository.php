<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\DriveRepository;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;

class FindTechnicalInspectionReportByIdDriveRepository
{
    public function __construct(
        private readonly TechnicalInspectionReportDriveRecordRepository $records,
    ) {}

    public function findById(TechnicalInspectionReportIdValueObject $id): ?TechnicalInspectionReportEntity
    {
        return $this->records->findReportById($id->value());
    }
}
