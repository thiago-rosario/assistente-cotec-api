<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Trait;

use App\TechnicalInspectionReport\Application\DTO\SearchTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;

trait FindByReportIdTrait
{
    /**
     * @return list<TechnicalInspectionReportGoogleSheetEntity>
     */
    private function findByReportId(SearchTechnicalInspectionReportCatalogInputDTO $input): array
    {
        $report = $this->repository->findByReportId($input);

        return $report === null ? [] : [$report];
    }
}
