<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\SheetRepository;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use App\TechnicalInspectionReport\Infra\Trait\HandlesTechnicalInspectionReportGoogleSheetRows;

class FindAllTechnicalInspectionReportGoogleSheetRepository
{
    use HandlesTechnicalInspectionReportGoogleSheetRows;

    /**
     * @return list<TechnicalInspectionReportGoogleSheetEntity>
     */
    public function findAllSheet(): array
    {
        return $this->readReports();
    }
}
