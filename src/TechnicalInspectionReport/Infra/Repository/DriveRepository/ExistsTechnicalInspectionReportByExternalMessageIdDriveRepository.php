<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\DriveRepository;

use App\TechnicalInspectionReport\Domain\ValueObject\ExternalMessageIdValueObject;

class ExistsTechnicalInspectionReportByExternalMessageIdDriveRepository
{
    public function __construct(
        private readonly TechnicalInspectionReportDriveRecordRepository $records,
    ) {}

    public function existsByExternalMessageId(ExternalMessageIdValueObject $externalMessageId): bool
    {
        return $this->records->existsByExternalMessageId($externalMessageId->value());
    }
}
