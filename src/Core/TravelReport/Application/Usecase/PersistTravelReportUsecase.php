<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Usecase;

use App\Core\TravelReport\Application\DTO\PersistTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Interface\Usecase\PersistTravelReportUsecaseInterface;
use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;

class PersistTravelReportUsecase implements PersistTravelReportUsecaseInterface
{
    public function __construct(
        private readonly TravelReportRepositoryInterface $repository,
    ) {}

    public function __invoke(PersistTravelReportInputDTO $dto): PersistTravelReportOutputDTO
    {
        $travelReport = TravelReportEntity::newSubmission(
            municipalityId: $dto->municipalityId,
            submittedByUserId: $dto->submittedByUserId,
            fileName: $dto->fileName,
            filePath: $dto->filePath,
            seiProcess: $dto->seiProcess,
            fileSize: $dto->fileSize,
            mimeType: $dto->mimeType,
        );

        $travelReportCreated = $this->repository->insert($travelReport);

        return new PersistTravelReportOutputDTO(
            id: $travelReportCreated->id,
            municipalityId: $travelReportCreated->municipalityId,
            submittedByUserId: $travelReportCreated->submittedByUserId,
            fileName: $travelReportCreated->fileName,
            filePath: $travelReportCreated->filePath,
            fileSize: $travelReportCreated->fileSize,
            mimeType: $travelReportCreated->mimeType,
            seiProcess: $travelReportCreated->seiProcess,
        );
    }
}
