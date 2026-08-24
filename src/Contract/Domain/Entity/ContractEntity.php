<?php

declare(strict_types=1);

namespace App\Contract\Domain\Entity;

use DateTimeImmutable;

class ContractEntity
{
    /**
     * @param  array<int, string>  $municipalities
     * @param  array<int, ValueAdditiveEntity>  $valueAdditives
     * @param  array<int, ContractReadjustmentEntity>  $readjustments
     * @param  array<int, ContractExecutionDeadlineEntity>  $executionDeadlines
     */
    public function __construct(
        public string $contractNumber,
        public ?string $company,
        public ?string $seiProcess,
        public array $municipalities = [],
        public array $valueAdditives = [],
        public array $readjustments = [],
        public array $executionDeadlines = [],
        public ?string $object = null,
        public ?float $initialValue = null,
        public ?float $updatedValue = null,
        public ?DateTimeImmutable $validityStartDate = null,
        public ?DateTimeImmutable $validityEndDate = null,
        public int|string|null $executionDeadline = null,
        public ?string $currentSituation = null,
    ) {}

    public function hasCompany(): bool
    {
        return filled($this->company);
    }

    public function hasSeiProcess(): bool
    {
        return filled($this->seiProcess);
    }

    public function hasMunicipalities(): bool
    {
        return $this->municipalities !== [];
    }

    public function hasValueAdditives(): bool
    {
        return $this->valueAdditives !== [];
    }

    public function hasReadjustments(): bool
    {
        return $this->readjustments !== [];
    }

    public function hasExecutionDeadlines(): bool
    {
        return $this->executionDeadlines !== [];
    }

    public function isRelatedToMunicipality(string $municipality): bool
    {
        $municipality = mb_strtoupper(trim($municipality));

        foreach ($this->municipalities as $relatedMunicipality) {
            if (mb_strtoupper(trim($relatedMunicipality)) === $municipality) {
                return true;
            }
        }

        return false;
    }

    public function toSearchableArray(): array
    {
        return [
            'contractNumber' => $this->contractNumber,
            'company' => $this->company,
            'seiProcess' => $this->seiProcess,
            'municipalities' => $this->municipalities,
            'valueAdditives' => $this->valueAdditives,
            'readjustments' => $this->readjustments,
            'executionDeadlines' => $this->executionDeadlines,
        ];
    }
}
