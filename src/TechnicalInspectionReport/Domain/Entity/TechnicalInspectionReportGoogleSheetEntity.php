<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\Entity;

use InvalidArgumentException;

class TechnicalInspectionReportGoogleSheetEntity
{
    public const string ReportIdColumn = 'ID DO RELATÓRIO';

    public const string ReportNameColumn = 'NOME DO RELATÓRIO';

    public const string MunicipalityColumn = 'MUNICÍPIO';

    public const string SeiProcessColumn = 'PROCESSO SEI';

    public const string HasSeiProcessColumn = 'POSSUI PROCESSO SEI';

    public const string InspectionDateColumn = 'DATA DA VIAGEM';

    public const string ResponsiblePersonColumn = 'RESPONSÁVEL';

    public const string DocumentLinkColumn = 'LINK DO RELATÓRIO';

    public function __construct(
        string $reportId,
        string $documentName,
        string $municipality,
        ?string $seiProcess,
        bool $hasSeiProcess,
        string $inspectionDate,
        string $responsiblePerson,
        string $documentLink,
        ?int $rowNumber = null,
    ) {
        $this->reportId = self::required($reportId, 'O identificador do relatório é obrigatório.');
        $this->documentName = self::required($documentName, 'O nome do relatório é obrigatório.');
        $this->municipality = self::required($municipality, 'O município do relatório é obrigatório.');
        $this->seiProcess = self::optional($seiProcess);
        $this->hasSeiProcess = $hasSeiProcess;
        $this->inspectionDate = self::required($inspectionDate, 'A data da viagem é obrigatória.');
        $this->responsiblePerson = self::required($responsiblePerson, 'O responsável pelo relatório é obrigatório.');
        $this->documentLink = self::required($documentLink, 'O link do relatório é obrigatório.');
        $this->rowNumber = $rowNumber;

        if ($rowNumber !== null && $rowNumber < 2) {
            throw new InvalidArgumentException('A linha da planilha deve ser maior ou igual a 2.');
        }
    }

    public readonly string $reportId;

    public readonly string $documentName;

    public readonly string $municipality;

    public readonly ?string $seiProcess;

    public readonly bool $hasSeiProcess;

    public readonly string $inspectionDate;

    public readonly string $responsiblePerson;

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
            documentName: $this->documentName,
            municipality: $this->municipality,
            seiProcess: $this->seiProcess,
            hasSeiProcess: $this->hasSeiProcess,
            inspectionDate: $this->inspectionDate,
            responsiblePerson: $this->responsiblePerson,
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
            self::ReportNameColumn => $this->documentName,
            self::MunicipalityColumn => $this->municipality,
            self::SeiProcessColumn => $this->seiProcess ?? '',
            self::HasSeiProcessColumn => $this->hasSeiProcess ? 'Sim' : 'Não',
            self::InspectionDateColumn => $this->inspectionDate,
            self::ResponsiblePersonColumn => $this->responsiblePerson,
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
        $seiProcess = self::nullableString($row[self::SeiProcessColumn] ?? null);

        return new self(
            reportId: (string) ($row[self::ReportIdColumn] ?? ''),
            documentName: (string) ($row[self::ReportNameColumn] ?? ''),
            municipality: (string) ($row[self::MunicipalityColumn] ?? ''),
            seiProcess: $seiProcess,
            hasSeiProcess: self::parseHasSeiProcess($row[self::HasSeiProcessColumn] ?? null, $seiProcess),
            inspectionDate: (string) ($row[self::InspectionDateColumn] ?? ''),
            responsiblePerson: (string) ($row[self::ResponsiblePersonColumn] ?? ''),
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
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function parseHasSeiProcess(mixed $value, ?string $seiProcess): bool
    {
        $normalized = is_scalar($value) ? mb_strtolower(trim((string) $value)) : '';

        return match ($normalized) {
            'sim', 's', 'yes', 'true', '1' => true,
            'não', 'nao', 'n', 'no', 'false', '0' => false,
            default => $seiProcess !== null,
        };
    }
}
