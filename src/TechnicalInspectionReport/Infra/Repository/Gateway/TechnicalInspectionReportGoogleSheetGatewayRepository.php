<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\Gateway;

use App\TechnicalInspectionReport\Application\DTO\RegisterTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Application\DTO\SearchTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportSheetRepositoryInterface;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\FindAllTechnicalInspectionReportGoogleSheetRepository;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\FindTechnicalInspectionReportByMunicipalityGoogleSheetRepository;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\FindTechnicalInspectionReportByProcessSeiGoogleSheetRepository;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\FindTechnicalInspectionReportByReportIdGoogleSheetRepository;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\RegisterTechnicalInspectionReportGoogleSheetRepository;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\UpdateTechnicalInspectionReportGoogleSheetRepository;

class TechnicalInspectionReportGoogleSheetGatewayRepository implements TechnicalInspectionReportSheetRepositoryInterface
{
    public function __construct(
        private readonly FindAllTechnicalInspectionReportGoogleSheetRepository $findAllRepository,
        private readonly FindTechnicalInspectionReportByMunicipalityGoogleSheetRepository $findByMunicipalityRepository,
        private readonly FindTechnicalInspectionReportByProcessSeiGoogleSheetRepository $findByProcessSeiRepository,
        private readonly FindTechnicalInspectionReportByReportIdGoogleSheetRepository $findByReportIdRepository,
        private readonly RegisterTechnicalInspectionReportGoogleSheetRepository $registerRepository,
        private readonly UpdateTechnicalInspectionReportGoogleSheetRepository $updateRepository,
    ) {}

    public function register(RegisterTechnicalInspectionReportCatalogInputDTO $input): void
    {
        $this->registerRepository->register($input);
    }

    /**
     * @return list<TechnicalInspectionReportGoogleSheetEntity>
     */
    public function findByMunicipality(SearchTechnicalInspectionReportCatalogInputDTO $input): array
    {
        if (blank($input->municipality)) {
            return [];
        }

        return $this->findByMunicipalityRepository->findByMunicipality(
            $this->findAllRepository->findAllSheet(),
            $input->municipality,
        );
    }

    /**
     * @return list<TechnicalInspectionReportGoogleSheetEntity>
     */
    public function findByProcessSei(SearchTechnicalInspectionReportCatalogInputDTO $input): array
    {
        if (blank($input->seiProcess)) {
            return [];
        }

        return $this->findByProcessSeiRepository->findByProcessSei(
            $this->findAllRepository->findAllSheet(),
            $input->seiProcess,
        );
    }

    public function findByReportId(SearchTechnicalInspectionReportCatalogInputDTO $input): ?TechnicalInspectionReportGoogleSheetEntity
    {
        if (blank($input->reportId)) {
            return null;
        }

        return $this->findByReportIdRepository->findByReportId(
            $this->findAllRepository->findAllSheet(),
            $input->reportId,
        );
    }

    public function update(RegisterTechnicalInspectionReportCatalogInputDTO $input): void
    {
        $this->updateRepository->update($input);
    }
}
