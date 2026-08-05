<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Usecase;

use App\TechnicalInspectionReport\Application\DTO\RegisterTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Application\DTO\StoreTechnicalInspectionReportInputDTO;
use App\TechnicalInspectionReport\Application\DTO\StoreTechnicalInspectionReportOutputDTO;
use App\TechnicalInspectionReport\Application\Interfaces\Factory\TechnicalInspectionReportGoogleSheetFactoryInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Storage\TechnicalInspectionReportFileStorageInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\StoreTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportSheetRepositoryInterface;

class StoreTechnicalInspectionReportUsecase implements StoreTechnicalInspectionReportUsecaseInterface
{
    public function __construct(
        private readonly TechnicalInspectionReportFileStorageInterface $fileStorage,
        private readonly TechnicalInspectionReportGoogleSheetFactoryInterface $factory,
        private readonly TechnicalInspectionReportSheetRepositoryInterface $sheetRepository,
    ) {}

    public function __invoke(StoreTechnicalInspectionReportInputDTO $input): StoreTechnicalInspectionReportOutputDTO
    {
        $storedFile = $this->fileStorage->store(
            $input->report,
            $input->documentPath,
        );

        $catalogEntry = $this->factory->create($input->report, $storedFile);

        $this->sheetRepository->register(
            new RegisterTechnicalInspectionReportCatalogInputDTO(
                sheet: $catalogEntry,
            ),
        );

        return new StoreTechnicalInspectionReportOutputDTO(
            report: $input->report,
            storedFile: $storedFile,
            catalogEntry: $catalogEntry,
        );
    }
}
