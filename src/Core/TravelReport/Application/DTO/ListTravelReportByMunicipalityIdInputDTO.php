<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\DTO;

readonly class ListTravelReportByMunicipalityIdInputDTO
{
    public function __construct(
        public int $municipalityId,
    ) {}
}
