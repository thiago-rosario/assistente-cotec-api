<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\SheetRepository;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use App\TechnicalInspectionReport\Infra\Trait\HandlesTechnicalInspectionReportGoogleSheetRows;

class FindTechnicalInspectionReportByProcessSeiGoogleSheetRepository
{
    use HandlesTechnicalInspectionReportGoogleSheetRows;

    /**
     * @param  list<TechnicalInspectionReportGoogleSheetEntity>  $reports
     * @return list<TechnicalInspectionReportGoogleSheetEntity>
     */
    public function findByProcessSei(array $reports, string $process): array
    {
        return array_values(array_filter(
            $reports,
            fn (TechnicalInspectionReportGoogleSheetEntity $report): bool => $this->matchesProcess(
                $report->seiProcess,
                $process,
            ),
        ));
    }
}
