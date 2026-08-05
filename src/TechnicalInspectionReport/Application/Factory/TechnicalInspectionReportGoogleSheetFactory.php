<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Factory;

use App\TechnicalInspectionReport\Application\DTO\StoredTechnicalInspectionReportFileDTO;
use App\TechnicalInspectionReport\Application\Interfaces\Factory\TechnicalInspectionReportGoogleSheetFactoryInterface;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;

final class TechnicalInspectionReportGoogleSheetFactory implements TechnicalInspectionReportGoogleSheetFactoryInterface
{
    public function create(
        TechnicalInspectionReportEntity $entity,
        StoredTechnicalInspectionReportFileDTO $storedFile,
    ): TechnicalInspectionReportGoogleSheetEntity {
        return new TechnicalInspectionReportGoogleSheetEntity(
            reportId: $entity->id()->value(),
            externalMessageId: $entity->externalMessageId()->value(),
            municipality: $entity->municipality()?->value() ?? '',
            seiProcess: $entity->seiProcess()?->value(),
            inspectionDate: $entity->inspectionDate()?->formatted() ?? '',
            responsiblePerson: $entity->responsiblePerson()?->value() ?? '',
            documentName: $entity->document()?->originalFileName() ?? '',
            documentId: $storedFile->id,
            documentLink: $storedFile->webViewLink,
        );
    }
}
