<?php

declare(strict_types=1);

namespace App\Contract\Domain\Entity;

use DateTimeImmutable;

class ContractExecutionDeadlineEntity
{
    public function __construct(
        public string $contractNumber,
        public ?string $company,
        public ?string $municipality,
        public ?string $seiProcess,
        public ?DateTimeImmutable $validityEndDate,
        public ?DateTimeImmutable $executionEndDate,
        public ?string $contractSituation,
        public ?string $deadlineAddendumStatus,
        public ?string $location,
        public ?DateTimeImmutable $publicationDate,
        public ?string $observation,
        public ?DateTimeImmutable $entryDate = null,
        public ?int $processingTimeDays = null,
        public ?int $publicationTimeDays = null,
        public ?string $unit = null,
    ) {}

    public function hasValidityEndDate(): bool
    {
        return $this->validityEndDate !== null;
    }

    public function hasExecutionEndDate(): bool
    {
        return $this->executionEndDate !== null;
    }

    public function hasDeadlineAddendum(): bool
    {
        return filled($this->deadlineAddendumStatus);
    }

    public function hasMunicipality(): bool
    {
        return filled($this->municipality)
            && trim((string) $this->municipality) !== '-';
    }

    public function isExecutionExpired(DateTimeImmutable $referenceDate): bool
    {
        return $this->executionEndDate !== null
            && $this->executionEndDate < $referenceDate;
    }

    public function isValidityExpired(DateTimeImmutable $referenceDate): bool
    {
        return $this->validityEndDate !== null
            && $this->validityEndDate < $referenceDate;
    }
}
