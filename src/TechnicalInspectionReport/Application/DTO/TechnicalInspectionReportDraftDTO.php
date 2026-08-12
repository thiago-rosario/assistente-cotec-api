<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\DTO;

readonly class TechnicalInspectionReportDraftDTO
{
    public function __construct(
        public string $reportId,
        public string $externalMessageId,
        public ?string $municipality = null,
        public ?bool $hasSeiProcess = null,
        public ?string $seiProcess = null,
        public ?string $inspectionDate = null,
        public ?string $responsiblePerson = null,
        public ?string $documentPath = null,
        public ?string $documentName = null,
        public ?string $documentMimeType = null,
        public ?int $documentSizeBytes = null,
    ) {}
}
