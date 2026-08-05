<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\DriveRepository;

use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;

class DeleteTechnicalInspectionReportDriveRepository
{
    public function __construct(
        private readonly TechnicalInspectionReportDriveRecordRepository $records,
    ) {}

    public function delete(TechnicalInspectionReportIdValueObject $id): void
    {
        $this->records->deleteByReportId($id->value());
    }
}
