<?php

declare(strict_types=1);

namespace App\Contract\Application\DTO;

readonly class ValueAdditiveOutputDTO
{
    public function __construct(
        public ?string $entryDate,
        public ?string $stage,
        public string $contractNumber,
        public ?string $company,
        public string $municipality,
        public ?string $unit,
        public ?string $seiProcess,
        public ?string $type,
        public ?float $value,
        public ?string $status,
        public ?string $currentLocation,
        public ?int $processingTimeDays,
        public ?string $situation,
        public ?string $publicationDate,
        public ?float $publishedValue,
        public ?int $publicationTimeDays,
        public ?string $additiveNumber,
        public ?string $observation,
    ) {}
}
