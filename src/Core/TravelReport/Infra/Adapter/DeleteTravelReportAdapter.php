<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Adapter;

use App\Core\TravelReport\Application\DTO\DeleteTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\DeleteTravelReportOutputDTO;
use App\Core\TravelReport\Application\Interface\Adapter\DeleteTravelReportAdapterInterface;

class DeleteTravelReportAdapter implements DeleteTravelReportAdapterInterface
{
    /**
     * @param  array{id: int|string}  $payload
     */
    public function toInput(array $payload): DeleteTravelReportInputDTO
    {
        return new DeleteTravelReportInputDTO(
            id: (int) $payload['id'],
        );
    }

    /**
     * @return array{id: int, deleted: bool}
     */
    public function toArray(DeleteTravelReportOutputDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'deleted' => $dto->deleted,
        ];
    }
}
