<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Interfaces\Storage;

use App\TechnicalInspectionReport\Application\DTO\StoredTechnicalInspectionReportFileDTO;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;

interface TechnicalInspectionReportFileStorageInterface
{
    public function store(
        TechnicalInspectionReportEntity $report,
        string $documentPath,
    ): StoredTechnicalInspectionReportFileDTO;

    public function delete(StoredTechnicalInspectionReportFileDTO $storedFile): void;
}
