<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\Repository;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\ValueObject\ExternalMessageIdValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;

interface TechnicalInspectionReportRepositoryInterface
{
    public function save(TechnicalInspectionReportEntity $report): void;

    public function findById(TechnicalInspectionReportIdValueObject $id): ?TechnicalInspectionReportEntity;

    public function existsByExternalMessageId(ExternalMessageIdValueObject $externalMessageId): bool;

    public function findAll(): array;

    public function findByMunicipality(string $municipality): array;

    public function delete(TechnicalInspectionReportIdValueObject $id): void;
}
