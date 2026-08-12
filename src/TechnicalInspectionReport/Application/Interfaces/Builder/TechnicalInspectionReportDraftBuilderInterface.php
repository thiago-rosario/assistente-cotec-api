<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Interfaces\Builder;

use App\TechnicalInspectionReport\Application\DTO\TechnicalInspectionReportDraftDTO;

interface TechnicalInspectionReportDraftBuilderInterface
{
    public function from(TechnicalInspectionReportDraftDTO $draft): self;

    public function withMunicipality(string $municipality): self;

    public function withSeiProcess(string $seiProcess): self;

    public function awaitingSeiProcess(): self;

    public function withoutSeiProcess(): self;

    public function withInspectionDate(string $inspectionDate): self;

    public function withResponsiblePerson(string $responsiblePerson): self;

    public function withDocument(
        string $documentPath,
        string $documentName,
        string $documentMimeType,
        int $documentSizeBytes,
    ): self;

    public function build(): TechnicalInspectionReportDraftDTO;
}
