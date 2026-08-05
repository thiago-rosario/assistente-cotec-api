<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\DTO;

readonly class SearchTechnicalInspectionReportCatalogInputDTO
{
    public function __construct(
        public ?string $reportId = null,
        public ?string $municipality = null,
        public ?string $seiProcess = null,
    ) {}
}
