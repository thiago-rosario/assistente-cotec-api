<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\DTO;

readonly class PersistTravelReportInputDTO
{
    public function __construct(
        public int $municipalityId,
        public string $submittedByUserId,
        public string $fileName,
        public string $filePath,
        public string $seiProcess,
        public ?int $fileSize = null,
        public string $mimeType = 'application/pdf',
    ) {}
}
