<?php

declare(strict_types=1);

namespace App\Core\Infra\Trait;

use Illuminate\Support\Str;

trait HandlesGoogleSheetRows
{
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
            ->toString();
    }

    private function spreadsheetId(): string
    {
        return (string) config('google_sheets.cotec_spreadsheet.spreadsheet_id');
    }

    private function sheetName(): string
    {
        return (string) config('google_sheets.cotec_spreadsheet.sheets.1843958344.name');
    }
}
