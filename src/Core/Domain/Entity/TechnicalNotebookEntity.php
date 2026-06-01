<?php

declare(strict_types=1);

namespace App\Core\Domain\Entity;

use DateTimeImmutable;

class TechnicalNotebookEntity
{
    public function __construct(
        public ?int $item,
        public ?string $stage,
        public string $municipality,
        public ?string $process,
        public ?string $force,
        public ?string $claim,
        public ?string $typology,
        public ?string $typologyObservation,
        public ?float $estimatedValue,
        public ?string $inspection,
        public ?string $seiReport,
        public ?string $landStatus,
        public ?string $landRegularization,
        public ?string $soilStudy,
        public ?string $environmental,
        public ?string $inspectionComment,
        public ?string $claimStage,
        public ?string $biddingSei,
        public ?string $contract,
        public ?string $fiplanInstrument,
        public ?string $buildStatus,
        public ?DateTimeImmutable $inaugurationDate,
    ) {}

    public function hasContract(): bool
    {
        return filled($this->contract);
    }

    public function isInaugurated(): bool
    {
        return mb_strtoupper((string) $this->buildStatus) === 'INAUGURADA';
    }

    public function hasEstimatedValue(): bool
    {
        return $this->estimatedValue !== null && $this->estimatedValue > 0;
    }
}
