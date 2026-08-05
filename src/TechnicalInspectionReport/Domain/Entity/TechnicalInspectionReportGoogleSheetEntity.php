<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\Entity;

use InvalidArgumentException;

class TechnicalInspectionReportGoogleSheetEntity
{
    public const string ReportIdColumn = 'ID DO RELATÓRIO';

    public const string ExternalMessageIdColumn = 'ID DA MENSAGEM';

    public const string MunicipalityColumn = 'MUNICÍPIO';

    public const string SeiProcessColumn = 'PROCESSO SEI';

    public const string InspectionDateColumn = 'DATA DA VISTORIA';

    public const string ResponsiblePersonColumn = 'RESPONSÁVEL';

    public const string DocumentNameColumn = 'NOME DO DOCUMENTO';

    public const string DocumentIdColumn = 'ID DO DOCUMENTO';

    public const string DocumentLinkColumn = 'LINK DO DOCUMENTO';

    public function __construct(
        string $reportId,
        string $externalMessageId,
        string $municipality,
        ?string $seiProcess,
        string $inspectionDate,
        string $responsiblePerson,
        string $documentName,
        string $documentId,
        string $documentLink,
        ?int $rowNumber = null,
    ) {
        $this->reportId = self::required($reportId, 'O identificador do relatório é obrigatório.');
        $this->externalMessageId = self::required($externalMessageId, 'O identificador da mensagem é obrigatório.');
        $this->municipality = self::required($municipality, 'O município do relatório é obrigatório.');
        $this->seiProcess = self::optional($seiProcess);
        $this->inspectionDate = self::required($inspectionDate, 'A data da vistoria é obrigatória.');
        $this->responsiblePerson = self::required($responsiblePerson, 'O responsável pelo relatório é obrigatório.');
        $this->documentName = self::required($documentName, 'O nome do documento é obrigatório.');
        $this->documentId = self::required($documentId, 'O identificador do documento é obrigatório.');
        $this->documentLink = self::required($documentLink, 'O link do documento é obrigatório.');
        $this->rowNumber = $rowNumber;

        if ($rowNumber !== null && $rowNumber < 2) {
            throw new InvalidArgumentException('A linha da planilha deve ser maior ou igual a 2.');
        }
    }

    public readonly string $reportId;

    public readonly string $externalMessageId;

    public readonly string $municipality;

    public readonly ?string $seiProcess;

    public readonly string $inspectionDate;

    public readonly string $responsiblePerson;

    public readonly string $documentName;

    public readonly string $documentId;

    public readonly string $documentLink;

    private readonly ?int $rowNumber;

    public function rowNumber(): ?int
    {
        return $this->rowNumber;
    }

    public function withRowNumber(int $rowNumber): self
    {
        return new self(
            reportId: $this->reportId,
            externalMessageId: $this->externalMessageId,
            municipality: $this->municipality,
            seiProcess: $this->seiProcess,
            inspectionDate: $this->inspectionDate,
            responsiblePerson: $this->responsiblePerson,
            documentName: $this->documentName,
            documentId: $this->documentId,
            documentLink: $this->documentLink,
            rowNumber: $rowNumber,
        );
    }

    /**
     * @return array<string, string>
     */
    public function toSheetRow(): array
    {
        return [
            self::ReportIdColumn => $this->reportId,
            self::ExternalMessageIdColumn => $this->externalMessageId,
            self::MunicipalityColumn => $this->municipality,
            self::SeiProcessColumn => $this->seiProcess ?? '',
            self::InspectionDateColumn => $this->inspectionDate,
            self::ResponsiblePersonColumn => $this->responsiblePerson,
            self::DocumentNameColumn => $this->documentName,
            self::DocumentIdColumn => $this->documentId,
            self::DocumentLinkColumn => $this->documentLink,
        ];
    }

    /**
     * @return list<string>
     */
    public function toOrderedSheetRow(): array
    {
        return array_values($this->toSheetRow());
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromSheetRow(array $row, int $rowNumber): self
    {
        return new self(
            reportId: (string) ($row[self::ReportIdColumn] ?? ''),
            externalMessageId: (string) ($row[self::ExternalMessageIdColumn] ?? ''),
            municipality: (string) ($row[self::MunicipalityColumn] ?? ''),
            seiProcess: self::nullableString($row[self::SeiProcessColumn] ?? null),
            inspectionDate: (string) ($row[self::InspectionDateColumn] ?? ''),
            responsiblePerson: (string) ($row[self::ResponsiblePersonColumn] ?? ''),
            documentName: (string) ($row[self::DocumentNameColumn] ?? ''),
            documentId: (string) ($row[self::DocumentIdColumn] ?? ''),
            documentLink: (string) ($row[self::DocumentLinkColumn] ?? ''),
            rowNumber: $rowNumber,
        );
    }

    private static function required(string $value, string $message): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException($message);
        }

        return $value;
    }

    private static function optional(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function nullableString(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }
}
