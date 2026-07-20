<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Adapter;

use App\Core\TravelReport\Application\DTO\ListTravelReportByMunicipalityIdInputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportsOutputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Interface\Adapter\ListTravelReportByMunicipalityIdAdapterInterface;

class ListTravelReportByMunicipalityIdAdapter implements ListTravelReportByMunicipalityIdAdapterInterface
{
    /**
     * @param  array{municipality_id: int|string}  $payload
     */
    public function toInput(array $payload): ListTravelReportByMunicipalityIdInputDTO
    {
        return new ListTravelReportByMunicipalityIdInputDTO(
            municipalityId: (int) $payload['municipality_id'],
        );
    }

    /**
     * @return array{total: int, data: list<array<string, mixed>>}
     */
    public function toArray(ListTravelReportsOutputDTO $dto): array
    {
        return [
            'total' => $dto->total,
            'data' => array_map(
                fn (PersistTravelReportOutputDTO $travelReport): array => $this->travelReportToArray($travelReport),
                $dto->data,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function travelReportToArray(PersistTravelReportOutputDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'municipality_id' => $dto->municipalityId,
            'submitted_by_user_id' => $dto->submittedByUserId,
            'file_name' => $dto->fileName,
            'file_path' => $dto->filePath,
            'sei_process' => $dto->seiProcess,
            'file_size' => $dto->fileSize,
            'mime_type' => $dto->mimeType,
        ];
    }
}
