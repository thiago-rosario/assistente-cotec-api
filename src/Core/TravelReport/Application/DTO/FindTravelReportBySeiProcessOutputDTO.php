<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\DTO;

readonly class FindTravelReportBySeiProcessOutputDTO
{
    public function __construct(
        public ?PersistTravelReportOutputDTO $data,
    ) {}
}
