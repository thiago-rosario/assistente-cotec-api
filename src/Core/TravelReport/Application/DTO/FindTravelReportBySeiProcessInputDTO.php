<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\DTO;

readonly class FindTravelReportBySeiProcessInputDTO
{
    public function __construct(
        public string $seiProcess,
    ) {}
}
