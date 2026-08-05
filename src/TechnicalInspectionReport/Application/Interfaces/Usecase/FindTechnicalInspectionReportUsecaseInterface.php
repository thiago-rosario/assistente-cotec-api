<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Interfaces\Usecase;

use App\TechnicalInspectionReport\Application\DTO\SearchTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;

interface FindTechnicalInspectionReportUsecaseInterface
{
    /**
     * @return list<TechnicalInspectionReportGoogleSheetEntity>
     */
    public function __invoke(SearchTechnicalInspectionReportCatalogInputDTO $input): array;
}
