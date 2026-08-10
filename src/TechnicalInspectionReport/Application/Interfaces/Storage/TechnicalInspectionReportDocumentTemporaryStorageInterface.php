<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Interfaces\Storage;

use App\Core\Domain\Entity\MessageDocumentEntity;
use App\TechnicalInspectionReport\Application\DTO\TechnicalInspectionReportTemporaryFileDTO;

interface TechnicalInspectionReportDocumentTemporaryStorageInterface
{
    public function store(
        MessageDocumentEntity $document,
        string $reportId,
    ): TechnicalInspectionReportTemporaryFileDTO;

    public function absolutePath(string $path): string;

    public function delete(string $path): void;
}
