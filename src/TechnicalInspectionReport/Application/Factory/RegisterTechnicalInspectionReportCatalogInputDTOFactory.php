<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Factory;

use App\TechnicalInspectionReport\Application\DTO\RegisterTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Application\DTO\StoredTechnicalInspectionReportFileDTO;
use App\TechnicalInspectionReport\Application\Interfaces\Factory\RegisterTechnicalInspectionReportCatalogInputDTOFactoryInterface;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Exception\InvalidReportForCatalogingException;

class RegisterTechnicalInspectionReportCatalogInputDTOFactory implements RegisterTechnicalInspectionReportCatalogInputDTOFactoryInterface
{
    public static function fromReportAndStoredFile(TechnicalInspectionReportEntity $report, StoredTechnicalInspectionReportFileDTO $storedFile): RegisterTechnicalInspectionReportCatalogInputDTO
    {
        if ($report->document() === null) {
            throw new InvalidReportForCatalogingException;
        }

        return new RegisterTechnicalInspectionReportCatalogInputDTO(
            sheet: (new TechnicalInspectionReportGoogleSheetFactory)->create($report, $storedFile),
        );
    }
}
