<?php

declare(strict_types=1);

namespace App\Core\Domain\Entity;

class TravelItineraryEntity
{
    public function __construct(
        public string $municipality,
        public ?string $process,
        public ?string $region,
        public ?string $unitClaim,
        public ?string $force,
        public ?string $requester,
        public ?string $landStatus,
        public ?string $progress,
        public ?string $focalPointContact,
        public ?string $route,
        public ?string $mapLink,
    ) {}

    public function hasMapLink(): bool
    {
        return filled($this->mapLink);
    }

    public function hasFocalPointContact(): bool
    {
        return filled($this->focalPointContact);
    }

    public function awaitsTechnicalVisit(): bool
    {
        return str_contains(
            mb_strtolower((string) $this->progress),
            'aguardando visita técnica'
        );
    }
}
