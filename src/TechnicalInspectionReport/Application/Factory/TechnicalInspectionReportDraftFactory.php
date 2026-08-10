<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Factory;

use App\Core\Domain\Entity\MessageEntity;
use App\TechnicalInspectionReport\Application\DTO\TechnicalInspectionReportDraftDTO;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\ValueObject\ExternalMessageIdValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\InspectionDateValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\MunicipalityValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\ResponsiblePersonValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\SeiProcessValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportFileValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;
use Illuminate\Support\Str;

final class TechnicalInspectionReportDraftFactory
{
    public function start(MessageEntity $message): TechnicalInspectionReportDraftDTO
    {
        return new TechnicalInspectionReportDraftDTO(
            reportId: (string) Str::uuid(),
            externalMessageId: $message->externalId() ?: 'whatsapp-'.Str::uuid(),
        );
    }

    public function toEntity(TechnicalInspectionReportDraftDTO $draft): TechnicalInspectionReportEntity
    {
        $report = TechnicalInspectionReportEntity::start(
            TechnicalInspectionReportIdValueObject::fromString($draft->reportId),
            ExternalMessageIdValueObject::fromString($draft->externalMessageId),
        );

        if ($draft->municipality !== null) {
            $report->provideMunicipality(new MunicipalityValueObject($draft->municipality));
        }

        if ($draft->hasSeiProcess === true && $draft->seiProcess !== null) {
            $report->provideSeiProcess(new SeiProcessValueObject($draft->seiProcess));
        } elseif ($draft->hasSeiProcess === false) {
            $report->declareNoSeiProcess();
        }

        if ($draft->inspectionDate !== null) {
            $report->provideInspectionDate(
                InspectionDateValueObject::fromBrazilianFormat($draft->inspectionDate),
            );
        }

        if ($draft->responsiblePerson !== null) {
            $report->provideResponsiblePerson(new ResponsiblePersonValueObject($draft->responsiblePerson));
        }

        if ($draft->documentName !== null && $draft->documentMimeType !== null && $draft->documentSizeBytes !== null) {
            $report->attachDocument(new TechnicalInspectionReportFileValueObject(
                $draft->documentName,
                $draft->documentMimeType,
                $draft->documentSizeBytes,
            ));
        }

        return $report;
    }
}
