<?php

declare(strict_types=1);

namespace App\Core\Infra\Trait;

use Illuminate\Support\Str;

trait HandlesGoogleSheetRows
{
    private const string TechnicalNotebookSheetKey = 'caderno-tecnico';

    private function combineHeader(array $header, array $row): array
    {
        $header = array_map(fn (mixed $value): string => trim((string) $value), $header);

        $row = array_pad($row, count($header), null);

        return array_combine($header, array_slice($row, 0, count($header))) ?: [];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function hasUsefulData(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
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

    private function municipalitiesMatch(string $candidate, string $municipality): bool
    {
        $normalizedCandidate = $this->normalizeMunicipality($candidate);
        $normalizedMunicipality = $this->normalizeMunicipality($municipality);

        if ($normalizedCandidate === '' || $normalizedMunicipality === '') {
            return false;
        }

        if ($normalizedCandidate === $normalizedMunicipality) {
            return true;
        }

        $allowedDistance = $this->allowedMunicipalityDistance($normalizedMunicipality);

        return $allowedDistance > 0
            && levenshtein($normalizedCandidate, $normalizedMunicipality) <= $allowedDistance;
    }

    private function normalizeMunicipality(string $value): string
    {
        return Str::of($this->normalize($value))
            ->replaceMatches('/\b(d[aeo]s?)\b/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }

    private function allowedMunicipalityDistance(string $normalizedMunicipality): int
    {
        $length = mb_strlen($normalizedMunicipality);

        return match (true) {
            $length >= 12 => 2,
            $length >= 5 => 1,
            default => 0,
        };
    }

    private function processesMatch(?string $candidate, string $process): bool
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

    /**
     * @return list<string>
     */
    private function extractSeiProcesses(string $value): array
    {
        preg_match_all('/\d{3}\.\d{4,5}\.\d{4}\.\d{7}-\d{2}/', $value, $matches);

        return array_values(array_unique($matches[0]));
    }

    private function spreadsheetId(): string
    {
        return (string) config('google_sheets.cotec_spreadsheet.spreadsheet_id');
    }

    private function sheetName(): string
    {
        foreach (config('google_sheets.cotec_spreadsheet.sheets', []) as $sheet) {
            if (is_array($sheet) && ($sheet['key'] ?? null) === self::TechnicalNotebookSheetKey) {
                return (string) ($sheet['name'] ?? '');
            }
        }

        return '';
    }
}
