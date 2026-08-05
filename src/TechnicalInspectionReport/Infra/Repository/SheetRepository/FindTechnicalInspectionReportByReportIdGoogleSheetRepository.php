<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\SheetRepository;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use App\TechnicalInspectionReport\Infra\Trait\HandlesTechnicalInspectionReportGoogleSheetRows;

class FindTechnicalInspectionReportByReportIdGoogleSheetRepository
{
    use HandlesTechnicalInspectionReportGoogleSheetRows;

    /**
     * @param  list<TechnicalInspectionReportGoogleSheetEntity>  $reports
     */
    public function findByReportId(array $reports, string $reportId): ?TechnicalInspectionReportGoogleSheetEntity
    {
        $normalizedReportId = $this->normalize($reportId);

        foreach ($reports as $report) {
            if ($this->normalize($report->reportId) === $normalizedReportId) {
                return $report;
            }
        }

        return null;
    }
}
