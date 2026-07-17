<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\DTO;

readonly class DeleteTravelReportInputDTO
{
    public function __construct(
        public int $id,
    ) {}
}
