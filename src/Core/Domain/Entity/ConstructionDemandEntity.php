<?php

declare(strict_types=1);

namespace App\Core\Domain\Entity;

class ConstructionDemandEntity
{
    public function __construct(
        public string $municipality,
        public ?string $force,
        public ?string $process,
        public ?string $unitClaim,
        public ?string $requesterDescription,
        public ?string $landStatus,
        public ?string $progress,
        public ?string $inspectionReport,
        public ?string $unitSizeClaim,
        public ?string $region,
        public ?string $requester,
        public ?string $soilSurveyAndTopography,
    ) {}

    public function hasProcess(): bool
    {
        return filled($this->process);
    }

    public function hasInspectionReport(): bool
    {
        return filled($this->inspectionReport);
    }

    public function shouldRequestSoilSurveyAndTopography(): bool
    {
        return mb_strtolower(trim((string) $this->soilSurveyAndTopography)) === 'solicitar';
    }

    /**
     * @return array<int, string|null>
     */
    public function toSearchableArray(): array
    {
        return [
            $this->municipality,
            $this->force,
            $this->process,
            $this->unitClaim,
            $this->requesterDescription,
            $this->landStatus,
            $this->progress,
            $this->inspectionReport,
            $this->unitSizeClaim,
            $this->region,
            $this->requester,
            $this->soilSurveyAndTopography,
        ];
    }
}
