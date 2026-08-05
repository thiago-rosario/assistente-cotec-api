<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\Repository;

use App\TechnicalInspectionReport\Application\DTO\RegisterTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Application\DTO\SearchTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;

interface TechnicalInspectionReportSheetRepositoryInterface
{
    public function register(RegisterTechnicalInspectionReportCatalogInputDTO $input): void;

    /**
     * @return list<TechnicalInspectionReportGoogleSheetEntity>
     */
    public function findByMunicipality(SearchTechnicalInspectionReportCatalogInputDTO $input): array;

    /**
     * @return list<TechnicalInspectionReportGoogleSheetEntity>
     */
    public function findByProcessSei(SearchTechnicalInspectionReportCatalogInputDTO $input): array;

    public function findByReportId(SearchTechnicalInspectionReportCatalogInputDTO $input): ?TechnicalInspectionReportGoogleSheetEntity;

    public function update(RegisterTechnicalInspectionReportCatalogInputDTO $input): void;
}
