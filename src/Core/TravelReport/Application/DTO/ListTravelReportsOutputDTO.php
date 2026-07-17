<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\DTO;

readonly class ListTravelReportsOutputDTO
{
    /**
     * @param  list<PersistTravelReportOutputDTO>  $data
     */
    public function __construct(
        public int $total,
        public array $data,
    ) {}
}
