<?php

declare(strict_types=1);

namespace App\Core\Domain\Entity;

class NotebookEntity
{
    public function __construct(
        public string $municipality,
        public ?string $relatedProcess,
        public ?string $unitClaim,
        public ?string $objectSize,
        public ?string $landStatus,
        public ?string $requester,
        public ?float $estimatedCost,
    ) {}

    public function hasRelatedProcess(): bool
    {
        return filled($this->relatedProcess);
    }

    public function hasEstimatedCost(): bool
    {
        return $this->estimatedCost !== null && $this->estimatedCost > 0;
    }

    public function hasDefinedObjectSize(): bool
    {
        return filled($this->objectSize)
            && mb_strtolower(trim((string) $this->objectSize)) !== 'a preencher';
    }

    /**
     * @return array<int, string|null>
     */
    public function toSearchableArray(): array
    {
        return [
            $this->municipality,
            $this->relatedProcess,
            $this->unitClaim,
            $this->objectSize,
            $this->landStatus,
            $this->requester,
        ];
    }
}
