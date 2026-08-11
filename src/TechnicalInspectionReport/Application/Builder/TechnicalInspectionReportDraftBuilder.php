<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Builder;

use App\TechnicalInspectionReport\Application\DTO\TechnicalInspectionReportDraftDTO;
use App\TechnicalInspectionReport\Application\Interfaces\Builder\TechnicalInspectionReportDraftBuilderInterface;

final class TechnicalInspectionReportDraftBuilder implements TechnicalInspectionReportDraftBuilderInterface
{
    private string $reportId = '';

    private string $externalMessageId = '';

    private ?string $municipality = null;

    private ?bool $hasSeiProcess = null;

    private ?string $seiProcess = null;

    private ?string $inspectionDate = null;

    private ?string $responsiblePerson = null;

    private ?string $documentPath = null;

    private ?string $documentName = null;

    private ?string $documentMimeType = null;

    private ?int $documentSizeBytes = null;

    public function from(TechnicalInspectionReportDraftDTO $draft): self
    {
        $this->reportId = $draft->reportId;
        $this->externalMessageId = $draft->externalMessageId;
        $this->municipality = $draft->municipality;
        $this->hasSeiProcess = $draft->hasSeiProcess;
        $this->seiProcess = $draft->seiProcess;
        $this->inspectionDate = $draft->inspectionDate;
        $this->responsiblePerson = $draft->responsiblePerson;
        $this->documentPath = $draft->documentPath;
        $this->documentName = $draft->documentName;
        $this->documentMimeType = $draft->documentMimeType;
        $this->documentSizeBytes = $draft->documentSizeBytes;

        return $this;
    }

    public function withMunicipality(string $municipality): self
    {
        $this->municipality = $municipality;

        return $this;
    }

    public function withSeiProcess(string $seiProcess): self
    {
        $this->hasSeiProcess = true;
        $this->seiProcess = $seiProcess;

        return $this;
    }

    public function awaitingSeiProcess(): self
    {
        $this->hasSeiProcess = true;
        $this->seiProcess = null;

        return $this;
    }

    public function withoutSeiProcess(): self
    {
        $this->hasSeiProcess = false;
        $this->seiProcess = null;

        return $this;
    }

    public function withInspectionDate(string $inspectionDate): self
    {
        $this->inspectionDate = $inspectionDate;

        return $this;
    }

    public function withResponsiblePerson(string $responsiblePerson): self
    {
        $this->responsiblePerson = $responsiblePerson;

        return $this;
    }

    public function withDocument(
        string $documentPath,
        string $documentName,
        string $documentMimeType,
        int $documentSizeBytes,
    ): self {
        $this->documentPath = $documentPath;
        $this->documentName = $documentName;
        $this->documentMimeType = $documentMimeType;
        $this->documentSizeBytes = $documentSizeBytes;

        return $this;
    }

    public function build(): TechnicalInspectionReportDraftDTO
    {
        return new TechnicalInspectionReportDraftDTO(
            reportId: $this->reportId,
            externalMessageId: $this->externalMessageId,
            municipality: $this->municipality,
            hasSeiProcess: $this->hasSeiProcess,
            seiProcess: $this->seiProcess,
            inspectionDate: $this->inspectionDate,
            responsiblePerson: $this->responsiblePerson,
            documentPath: $this->documentPath,
            documentName: $this->documentName,
            documentMimeType: $this->documentMimeType,
            documentSizeBytes: $this->documentSizeBytes,
        );
    }
}
