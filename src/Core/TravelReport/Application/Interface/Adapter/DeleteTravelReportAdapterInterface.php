<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Interface\Adapter;

use App\Core\TravelReport\Application\DTO\DeleteTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\DeleteTravelReportOutputDTO;

interface DeleteTravelReportAdapterInterface
{
    /**
     * @param  array{id: int|string}  $payload
     */
    public function toInput(array $payload): DeleteTravelReportInputDTO;

    /**
     * @return array{id: int, deleted: bool}
     */
    public function toArray(DeleteTravelReportOutputDTO $dto): array;
}
