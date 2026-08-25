<?php

declare(strict_types=1);

namespace App\Contract\Application\DTO;

use DateTimeImmutable;

readonly class ContractReadjustmentOutputDTO
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
}
