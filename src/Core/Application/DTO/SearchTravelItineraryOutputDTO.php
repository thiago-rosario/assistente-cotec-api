<?php

declare(strict_types=1);

namespace App\Core\Application\DTO;

readonly class SearchTravelItineraryOutputDTO
{
    /**
     * @param  list<array<string, mixed>>  $data
     */
    public function __construct(
        public ?string $term,
        public int $total,
        public array $data,
    ) {}
}
