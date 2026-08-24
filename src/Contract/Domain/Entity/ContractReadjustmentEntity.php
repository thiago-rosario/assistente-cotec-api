<?php

declare(strict_types=1);

namespace App\Contract\Domain\Entity;

use DateTimeImmutable;

class ContractReadjustmentEntity
{
    public function __construct(
        public ?DateTimeImmutable $entryDate,
        public ?string $company,
        public ?DateTimeImmutable $ceirfEntryDate,
        public ?DateTimeImmutable $ceirfLastMovementDate,
        public string $contractNumber,
        public ?string $seiProcess,
        public ?string $apostilleNumber,
        public ?float $contemplatedValue,
        public ?string $contemplatedIncidencePeriod,
        public ?string $status,
        public ?string $location,
        public ?int $processingTimeDays,
        public ?DateTimeImmutable $publicationDate,
        public ?int $publicationTimeDays,
        public ?string $observation,
        public ?string $paymentSituation,
        public ?string $paymentSei,
    ) {}

    public function hasContemplatedValue(): bool
    {
        return $this->contemplatedValue !== null && $this->contemplatedValue > 0;
    }

    public function hasApostilleNumber(): bool
    {
        return filled($this->apostilleNumber);
    }

    public function isPublished(): bool
    {
        return mb_strtoupper(trim((string) $this->status)) === 'PUBLICADO';
    }

    public function hasPaymentProcess(): bool
    {
        return filled($this->paymentSei)
            && ! in_array(trim((string) $this->paymentSei), ['-', '/'], true);
    }
}
