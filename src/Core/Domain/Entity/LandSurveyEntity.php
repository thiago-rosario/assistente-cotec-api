<?php

declare(strict_types=1);

namespace App\Core\Domain\Entity;

use DateTimeImmutable;

class LandSurveyEntity
{
    public function __construct(
        public string $municipality,
        public ?string $process,
        public ?string $region,
        public ?string $unitSizeClaim,
        public ?string $force,
        public ?string $requester,
        public ?string $ownership,
        public ?string $topography,
        public ?string $landStatus,
        public ?string $progress,
        public ?string $municipalityFocalPointContact,
        public ?string $militaryPoliceFocalPointContact,
        public ?string $civilPoliceFocalPointContact,
        public ?string $documentationLink,
        public ?DateTimeImmutable $updatedAt,
        public ?string $observations,
        public ?DateTimeImmutable $requestedAt,
    ) {}

    public function hasDocumentationLink(): bool
    {
        return filled($this->documentationLink);
    }

    public function hasPoliceFocalPointContact(): bool
    {
        return filled($this->militaryPoliceFocalPointContact)
            || filled($this->civilPoliceFocalPointContact);
    }

    public function hasTopography(): bool
    {
        return filled($this->topography);
    }

    /**
     * @return array<int, string|null>
     */
    public function toSearchableArray(): array
    {
        return [
            $this->municipality,
            $this->process,
            $this->region,
            $this->unitSizeClaim,
            $this->force,
            $this->requester,
            $this->ownership,
            $this->topography,
            $this->landStatus,
            $this->progress,
            $this->municipalityFocalPointContact,
            $this->militaryPoliceFocalPointContact,
            $this->civilPoliceFocalPointContact,
            $this->documentationLink,
            $this->observations,
        ];
    }
}
