<?php

declare(strict_types=1);

namespace App\Core\Application\DTO;

readonly class SearchConstructionDemandInputDTO
{
    public function __construct(
        public ?string $process = null,
        public ?string $municipality = null,
        public ?string $force = null,
        public ?string $region = null,
        public ?string $landStatus = null,
        public ?string $progress = null,
        public ?string $term = null,
    ) {}
}
