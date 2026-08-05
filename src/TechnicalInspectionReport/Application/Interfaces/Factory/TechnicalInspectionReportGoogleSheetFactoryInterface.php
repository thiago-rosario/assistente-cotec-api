<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Interfaces\Factory;

use App\TechnicalInspectionReport\Application\DTO\StoredTechnicalInspectionReportFileDTO;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;

interface TechnicalInspectionReportGoogleSheetFactoryInterface
{
    public function create(TechnicalInspectionReportEntity $entity, StoredTechnicalInspectionReportFileDTO $storedFile): TechnicalInspectionReportGoogleSheetEntity;
}
