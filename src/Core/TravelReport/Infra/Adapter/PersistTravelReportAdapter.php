<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Adapter;

use App\Core\TravelReport\Application\DTO\PersistTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Interface\Adapter\PersistTravelReportAdapterInterface;

class PersistTravelReportAdapter implements PersistTravelReportAdapterInterface
{
    /**
     * @param  array{
     *     municipality_id: int|string,
     *     submitted_by_user_id: string,
     *     file_name: string,
     *     file_path: string,
     *     file_size?: int|string|null,
     *     mime_type?: string|null,
     *     sei_process: string
     * }  $payload
     */
    public function toInput(array $payload): PersistTravelReportInputDTO
    {
        return new PersistTravelReportInputDTO(
            municipalityId: (int) $payload['municipality_id'],
            submittedByUserId: (string) $payload['submitted_by_user_id'],
            fileName: (string) $payload['file_name'],
            filePath: (string) $payload['file_path'],
            seiProcess: (string) $payload['sei_process'],
            fileSize: isset($payload['file_size']) ? (int) $payload['file_size'] : null,
            mimeType: (string) ($payload['mime_type'] ?? 'application/pdf'),
        );
    }

    /**
     * @return array{
     *     id: int|null,
     *     municipality_id: int,
     *     submitted_by_user_id: string,
     *     file_name: string,
     *     file_path: string,
     *     file_size: int|null,
     *     mime_type: string,
     *     sei_process: string
     * }
     */
    public function toArray(PersistTravelReportOutputDTO $dto): array
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
