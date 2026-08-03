<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\Entity;

use App\TechnicalInspectionReport\Domain\ValueObject\ExternalMessageIdValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\InspectionDateValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\MunicipalityValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\ResponsiblePersonValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\SeiProcessValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportFileValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;
use App\TechnicalInspectionReport\Enum\SeiProcessDecision;
use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportStatus;
use App\TechnicalInspectionReport\Exception\IncompleteTechnicalInspectionReportException;
use App\TechnicalInspectionReport\Exception\InvalidTechnicalInspectionReportStateTransitionException;

class TechnicalInspectionReportEntity
{
    private TechnicalInspectionReportStatus $status = TechnicalInspectionReportStatus::Draft;

    private SeiProcessDecision $seiProcessDecision = SeiProcessDecision::Pending;

    private ?MunicipalityValueObject $municipality = null;

    private ?SeiProcessValueObject $seiProcess = null;

    private ?InspectionDateValueObject $inspectionDate = null;

    private ?ResponsiblePersonValueObject $responsiblePerson = null;

    private ?TechnicalInspectionReportFileValueObject $document = null;

    private ?string $storageFailureReason = null;

    private function __construct(
        private readonly TechnicalInspectionReportIdValueObject $id,
        private readonly ExternalMessageIdValueObject $externalMessageId,
    ) {}

    public static function start(
        TechnicalInspectionReportIdValueObject $id,
        ExternalMessageIdValueObject $externalMessageId,
    ): self {
        return new self($id, $externalMessageId);
    }

    public function id(): TechnicalInspectionReportIdValueObject
    {
        return $this->id;
    }

    public function externalMessageId(): ExternalMessageIdValueObject
    {
        return $this->externalMessageId;
    }

    public function status(): TechnicalInspectionReportStatus
    {
        return $this->status;
    }

    public function municipality(): ?MunicipalityValueObject
    {
        return $this->municipality;
    }

    public function seiProcess(): ?SeiProcessValueObject
    {
        return $this->seiProcess;
    }

    public function inspectionDate(): ?InspectionDateValueObject
    {
        return $this->inspectionDate;
    }

    public function responsiblePerson(): ?ResponsiblePersonValueObject
    {
        return $this->responsiblePerson;
    }

    public function document(): ?TechnicalInspectionReportFileValueObject
    {
        return $this->document;
    }

    public function storageFailureReason(): ?string
    {
        return $this->storageFailureReason;
    }

    public function provideMunicipality(MunicipalityValueObject $municipality): self
    {
        $this->ensureDraft();
        $this->municipality = $municipality;

        return $this;
    }

    public function provideSeiProcess(SeiProcessValueObject $seiProcess): self
    {
        $this->ensureDraft();
        $this->seiProcess = $seiProcess;
        $this->seiProcessDecision = SeiProcessDecision::Provided;

        return $this;
    }

    public function declareNoSeiProcess(): self
    {
        $this->ensureDraft();
        $this->seiProcess = null;
        $this->seiProcessDecision = SeiProcessDecision::NotProvided;

        return $this;
    }

    public function provideInspectionDate(InspectionDateValueObject $inspectionDate): self
    {
        $this->ensureDraft();
        $this->inspectionDate = $inspectionDate;

        return $this;
    }

    public function provideResponsiblePerson(ResponsiblePersonValueObject $responsiblePerson): self
    {
        $this->ensureDraft();
        $this->responsiblePerson = $responsiblePerson;

        return $this;
    }

    public function attachDocument(TechnicalInspectionReportFileValueObject $document): self
    {
        $this->ensureDraft();
        $this->document = $document;

        return $this;
    }

    public function hasSeiProcess(): bool
    {
        return $this->seiProcessDecision === SeiProcessDecision::Provided;
    }

    public function hasDeclaredNoSeiProcess(): bool
    {
        return $this->seiProcessDecision === SeiProcessDecision::NotProvided;
    }

    public function hasSeiProcessDecision(): bool
    {
        return $this->seiProcessDecision !== SeiProcessDecision::Pending;
    }

    public function isComplete(): bool
    {
        return $this->municipality !== null
            && $this->hasSeiProcessDecision()
            && $this->inspectionDate !== null
            && $this->responsiblePerson !== null
            && $this->document !== null;
    }

    public function markReadyForStorage(): self
    {
        $this->ensureStatus(TechnicalInspectionReportStatus::Draft);

        if (! $this->isComplete()) {
            throw new IncompleteTechnicalInspectionReportException(
                'O relatório de vistoria técnica não possui todos os dados obrigatórios.',
            );
        }

        $this->status = TechnicalInspectionReportStatus::ReadyForStorage;

        return $this;
    }

    public function beginStorage(): self
    {
        $this->ensureStatus(TechnicalInspectionReportStatus::ReadyForStorage);
        $this->status = TechnicalInspectionReportStatus::StoragePending;

        return $this;
    }

    public function confirmStorage(): self
    {
        $this->ensureStatus(TechnicalInspectionReportStatus::StoragePending);
        $this->status = TechnicalInspectionReportStatus::Stored;

        return $this;
    }

    public function registerStorageFailure(?string $reason = null): self
    {
        $this->ensureStatus(TechnicalInspectionReportStatus::StoragePending);
        $reason = $reason === null ? null : trim($reason);
        $this->storageFailureReason = $reason === '' ? null : $reason;
        $this->status = TechnicalInspectionReportStatus::StorageFailed;

        return $this;
    }

    public function isStored(): bool
    {
        return $this->status === TechnicalInspectionReportStatus::Stored;
    }

    public function equals(self $report): bool
    {
        return $this->id->equals($report->id);
    }

    private function ensureDraft(): void
    {
        $this->ensureStatus(TechnicalInspectionReportStatus::Draft);
    }

    private function ensureStatus(TechnicalInspectionReportStatus $expected): void
    {
        if ($this->status === $expected) {
            return;
        }

        throw new InvalidTechnicalInspectionReportStateTransitionException(
            sprintf(
                'A operação não é permitida quando o relatório está no estado %s.',
                $this->status->value,
            ),
        );
    }
}
