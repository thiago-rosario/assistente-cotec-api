<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\DTO;

readonly class PersistTravelReportOutputDTO
{
    public function __construct(
        public ?int $id,
        public int $municipalityId,
        public string $submittedByUserId,
        public string $fileName,
        public string $filePath,
        public ?int $fileSize,
        public string $mimeType,
        public string $seiProcess,
    ) {}
}
