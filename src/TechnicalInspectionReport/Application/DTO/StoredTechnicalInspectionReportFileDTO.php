<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\DTO;

readonly class StoredTechnicalInspectionReportFileDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $mimeType,
        public int $sizeBytes,
        public string $webViewLink,
    ) {}
}
