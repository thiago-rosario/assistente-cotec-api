<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Adapter;

use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessInputDTO;
use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessOutputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Interface\Adapter\FindTravelReportBySeiProcessAdapterInterface;

class FindTravelReportBySeiProcessAdapter implements FindTravelReportBySeiProcessAdapterInterface
{
    /**
     * @param  array{sei_process: string}  $payload
     */
    public function toInput(array $payload): FindTravelReportBySeiProcessInputDTO
    {
        return new FindTravelReportBySeiProcessInputDTO(
            seiProcess: (string) $payload['sei_process'],
        );
    }

    /**
     * @return array{data: array<string, mixed>|null}
     */
    public function toArray(FindTravelReportBySeiProcessOutputDTO $dto): array
    {
        return [
            'data' => $dto->data instanceof PersistTravelReportOutputDTO
                ? $this->travelReportToArray($dto->data)
                : null,
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
