<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Trait;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Revolution\Google\Sheets\Facades\Sheets;

trait HandlesTechnicalInspectionReportGoogleSheetRows
{
    private const string ReadRange = 'A:ZZ';

    /**
     * @return list<TechnicalInspectionReportGoogleSheetEntity>
     */
    private function readReports(): array
    {
        return $this->readRows()
            ->map(fn (array $row): TechnicalInspectionReportGoogleSheetEntity => TechnicalInspectionReportGoogleSheetEntity::fromSheetRow(
                $row['values'],
                $row['rowNumber'],
            ))
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array{rowNumber: int, values: array<string, mixed>}>
     */
    private function readRows(): Collection
    {
        $rows = Sheets::spreadsheet($this->spreadsheetId())
            ->sheet($this->sheetName())
            ->range(self::ReadRange)
            ->get();

        $rows = $rows instanceof Collection ? $rows->values() : collect($rows)->values();

        if ($rows->isEmpty()) {
            return collect();
        }

        $headerIndex = $rows->search(
            fn (mixed $row): bool => $this->isHeaderRow($this->toArray($row)),
        );

        if ($headerIndex === false) {
            return collect();
        }

        $header = $this->toArray($rows->get($headerIndex));

        return $rows
            ->slice($headerIndex + 1)
            ->values()
            ->map(function (mixed $row, int $offset) use ($header, $headerIndex): array {
                return [
                    'rowNumber' => $headerIndex + $offset + 2,
                    'values' => $this->combineHeader($header, $this->toArray($row)),
                ];
            })
            ->filter(fn (array $row): bool => $this->hasUsefulData($row['values']))
            ->values();
    }

    private function spreadsheetId(): string
    {
        $spreadsheetId = trim((string) config('technical_inspection_report.google_sheet.spreadsheet_id'));

        if ($spreadsheetId === '') {
            throw new InvalidArgumentException(
                'O identificador da planilha de relatórios de vistoria técnica não está configurado.',
            );
        }

        return $spreadsheetId;
    }

    private function sheetName(): string
    {
        $sheetName = trim((string) config('technical_inspection_report.google_sheet.sheet_name'));

        if ($sheetName === '') {
            throw new InvalidArgumentException(
                'O nome da aba de relatórios de vistoria técnica não está configurado.',
            );
        }

        return $sheetName;
    }

    /**
     * @param  array<int, mixed>  $header
     * @param  array<int, mixed>  $row
     * @return array<string, mixed>
     */
    private function combineHeader(array $header, array $row): array
    {
        $header = $this->canonicalHeaders($header);
        $row = array_pad($row, count($header), null);

        return array_combine($header, array_slice($row, 0, count($header))) ?: [];
    }

    /**
     * @param  array<int, mixed>  $header
     * @return array<int, string>
     */
    private function canonicalHeaders(array $header): array
    {
        $columns = [
            TechnicalInspectionReportGoogleSheetEntity::ReportIdColumn,
            TechnicalInspectionReportGoogleSheetEntity::ReportNameColumn,
            TechnicalInspectionReportGoogleSheetEntity::MunicipalityColumn,
            TechnicalInspectionReportGoogleSheetEntity::SeiProcessColumn,
            TechnicalInspectionReportGoogleSheetEntity::HasSeiProcessColumn,
            TechnicalInspectionReportGoogleSheetEntity::InspectionDateColumn,
            TechnicalInspectionReportGoogleSheetEntity::ResponsiblePersonColumn,
            TechnicalInspectionReportGoogleSheetEntity::DocumentLinkColumn,
        ];
        $normalizedColumns = [];

        foreach ($columns as $column) {
            $normalizedColumns[$this->normalize($column)] = $column;
        }

        return array_map(function (mixed $value) use ($normalizedColumns): string {
            $header = trim((string) $value);

            return $normalizedColumns[$this->normalize($header)] ?? $header;
        }, $header);
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function isHeaderRow(array $row): bool
    {
        $columns = array_map(
            fn (mixed $value): string => $this->normalize((string) $value),
            $row,
        );

        return collect([
            TechnicalInspectionReportGoogleSheetEntity::ReportIdColumn,
            TechnicalInspectionReportGoogleSheetEntity::ReportNameColumn,
            TechnicalInspectionReportGoogleSheetEntity::MunicipalityColumn,
            TechnicalInspectionReportGoogleSheetEntity::SeiProcessColumn,
            TechnicalInspectionReportGoogleSheetEntity::HasSeiProcessColumn,
            TechnicalInspectionReportGoogleSheetEntity::InspectionDateColumn,
            TechnicalInspectionReportGoogleSheetEntity::ResponsiblePersonColumn,
            TechnicalInspectionReportGoogleSheetEntity::DocumentLinkColumn,
        ])->every(fn (string $column): bool => in_array($this->normalize($column), $columns, true));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function hasUsefulData(array $row): bool
    {
        foreach ($row as $value) {
            if (is_scalar($value) && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    private function matchesMunicipality(string $candidate, string $municipality): bool
    {
        $normalizedCandidate = $this->normalizeMunicipality($candidate);
        $normalizedMunicipality = $this->normalizeMunicipality($municipality);

        if ($normalizedCandidate === '' || $normalizedMunicipality === '') {
            return false;
        }

        if ($normalizedCandidate === $normalizedMunicipality) {
            return true;
        }

        $allowedDistance = match (true) {
            mb_strlen($normalizedMunicipality) >= 12 => 2,
            mb_strlen($normalizedMunicipality) >= 5 => 1,
            default => 0,
        };

        return $allowedDistance > 0
            && levenshtein($normalizedCandidate, $normalizedMunicipality) <= $allowedDistance;
    }

    private function matchesProcess(?string $candidate, string $process): bool
    {
        if ($candidate === null) {
            return false;
        }

        $normalizedCandidate = $this->normalize($candidate);
        $normalizedProcess = $this->normalize($process);

        if ($normalizedCandidate === $normalizedProcess) {
            return true;
        }

        $candidateProcesses = $this->extractSeiProcesses($candidate);
        $searchedProcesses = $this->extractSeiProcesses($process);

        if ($candidateProcesses !== [] && $searchedProcesses !== []) {
            return array_intersect($candidateProcesses, $searchedProcesses) !== [];
        }

        return str_contains($normalizedCandidate, $normalizedProcess);
    }

    private function normalizeMunicipality(string $value): string
    {
        return Str::of($this->normalize($value))
            ->replaceMatches('/\b(d[aeo]s?)\b/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->trim()
            ->lower()
            ->ascii()
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }

    /**
     * @return list<string>
     */
    private function extractSeiProcesses(string $value): array
    {
        preg_match_all('/\d{3}\.\d{4,5}\.\d{4}\.\d{7}-\d{2}/', $value, $matches);

        return array_values(array_unique($matches[0]));
    }

    /**
     * @return array<int, mixed>
     */
    private function toArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        return [];
    }
}
