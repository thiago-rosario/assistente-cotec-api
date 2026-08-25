<?php

declare(strict_types=1);

namespace App\Contract\Application\DTO;

use DateTimeImmutable;

readonly class ContractSummaryOutputDTO
{
    /**
     * @param  list<string>  $municipalities
     * @param  list<ValueAdditiveOutputDTO>  $valueAdditives
     * @param  list<ContractReadjustmentOutputDTO>  $readjustments
     * @param  list<ContractExecutionDeadlineOutputDTO>  $executionDeadlines
     * @param  list<string>  $processes
     * @param  list<string>  $statuses
     * @param  list<string>  $observations
     */
    public function __construct(
        public string $contractNumber,
        public ?string $company,
        public ?string $seiProcess,
        public array $municipalities = [],
        public ?string $municipality = null,
        public ?string $object = null,
        public ?float $initialValue = null,
        public ?float $updatedValue = null,
        public ?DateTimeImmutable $validityStartDate = null,
        public ?DateTimeImmutable $validityEndDate = null,
        public int|string|DateTimeImmutable|null $executionDeadline = null,
        public ?string $currentSituation = null,
        public array $valueAdditives = [],
        public array $readjustments = [],
        public array $executionDeadlines = [],
        public array $processes = [],
        public array $statuses = [],
        public array $observations = [],
        public int $additivesCount = 0,
    ) {}
}
