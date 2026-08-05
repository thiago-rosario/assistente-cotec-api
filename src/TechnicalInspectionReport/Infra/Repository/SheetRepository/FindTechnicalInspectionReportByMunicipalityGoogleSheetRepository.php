<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\SheetRepository;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use App\TechnicalInspectionReport\Infra\Trait\HandlesTechnicalInspectionReportGoogleSheetRows;

class FindTechnicalInspectionReportByMunicipalityGoogleSheetRepository
{
    use HandlesTechnicalInspectionReportGoogleSheetRows;

    /**
     * @param  list<TechnicalInspectionReportGoogleSheetEntity>  $reports
     * @return list<TechnicalInspectionReportGoogleSheetEntity>
     */
    public function findByMunicipality(array $reports, string $municipality): array
    {
        return array_values(array_filter(
            $reports,
            fn (TechnicalInspectionReportGoogleSheetEntity $report): bool => $this->matchesMunicipality(
                $report->municipality,
                $municipality,
            ),
        ));
    }
}
