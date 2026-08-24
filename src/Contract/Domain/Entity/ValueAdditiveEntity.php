<?php

declare(strict_types=1);

namespace App\Contract\Domain\Entity;

class ValueAdditiveEntity
{
    public function __construct(
        public string $contractNumber,
        public string $municipality,
        public ?string $company,
        public ?string $seiProcess,
        public ?string $stage,
        public ?string $unit,
        public ?string $type,
        public ?float $value,
        public ?string $status,
        public ?string $currentLocation,
        public ?string $situation,
        public ?string $publicationDate,
        public ?float $publishedValue,
        public ?string $additiveNumber,
        public ?string $observation,
    ) {}

    public function isIncrease(): bool
    {
        return mb_strtoupper(trim((string) $this->type)) === 'ACRÉSCIMO';
    }

    public function isSuppression(): bool
    {
        return mb_strtoupper(trim((string) $this->type)) === 'SUPRESSÃO';
    }

    public function hasValue(): bool
    {
        return $this->value !== null && $this->value !== 0;
    }

    public function hasAdditiveNumber(): bool
    {
        return filled($this->additiveNumber);
    }
}
