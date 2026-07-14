<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\DTO;

readonly class SearchTechnicalNotebookInputDTO
{
    public function __construct(
        public ?string $process = null,
        public ?string $municipality = null,
        public ?string $force = null,
        public ?string $buildStatus = null,
        public ?string $term = null,
    ) {}
}
