<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application;

use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Domain\Entity\TravelReportEntity;

trait ToOutputTrait
{
    private function toOutput(TravelReportEntity $travelReport): PersistTravelReportOutputDTO
    {
        return new PersistTravelReportOutputDTO(
            id: $travelReport->id,
            municipalityId: $travelReport->municipalityId,
            submittedByUserId: $travelReport->submittedByUserId,
            fileName: $travelReport->fileName,
            filePath: $travelReport->filePath,
            fileSize: $travelReport->fileSize,
            mimeType: $travelReport->mimeType,
            seiProcess: $travelReport->seiProcess,
        );
    }
}
