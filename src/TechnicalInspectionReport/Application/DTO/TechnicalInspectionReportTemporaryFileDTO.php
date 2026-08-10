<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\DTO;

readonly class TechnicalInspectionReportTemporaryFileDTO
{
    public function __construct(
        public string $path,
        public int $sizeBytes,
    ) {}
}
