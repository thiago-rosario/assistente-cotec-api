<?php

declare(strict_types=1);

namespace App\Contract\Application\DTO;

use DateTimeImmutable;

readonly class ContractExtractDTO
{
    public function __construct(
        public string $contractNumber,
        public ?string $company,
        public ?string $municipality = null,
        public ?string $seiProcess = null,
        public ?string $currentSituation = null,
        public ?float $updatedValue = null,
        public int $additivesCount = 0,
        public ?string $additivesStatus = null,
        public int $readjustmentsCount = 0,
        public ?string $readjustmentsStatus = null,
        public ?string $executionDeadlinesStatus = null,
        public ?DateTimeImmutable $lastMovementDate = null,
        public ?string $currentPending = null,
    ) {}
}
