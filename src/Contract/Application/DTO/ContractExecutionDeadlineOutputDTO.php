<?php

declare(strict_types=1);

namespace App\Contract\Application\DTO;

use DateTimeImmutable;

readonly class ContractExecutionDeadlineOutputDTO
{
    public function __construct(
        public ?DateTimeImmutable $entryDate,
        public ?string $company,
        public string $contractNumber,
        public ?DateTimeImmutable $validityEndDate,
        public ?string $municipality,
        public ?string $unit,
        public ?DateTimeImmutable $executionEndDate,
        public ?int $remainingExecutionDays,
        public ?int $remainingValidityDays,
        public ?string $contractSituation,
        public ?string $seiProcess,
        public ?string $location,
        public ?string $deadlineAddendumStatus,
        public ?int $processingTimeDays,
        public ?DateTimeImmutable $publicationDate,
        public ?int $publicationTimeDays,
        public ?string $observation,
    ) {}
}
