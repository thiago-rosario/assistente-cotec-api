<?php

declare(strict_types=1);

namespace App\Core\Infra\Adapter;

use App\Core\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\Core\Application\DTO\ReadGoogleSpreadsheetOutputDTO;
use App\Core\Application\Interfaces\ReadGoogleSpreadsheetAdapterInterface;
use Illuminate\Support\Collection;

class ReadGoogleSpreadsheetAdapter implements ReadGoogleSpreadsheetAdapterInterface
{
    /**
     * @param  array{spreadsheet_id: string, sheets: array<int, string|array{name: string}>}  $payload
     */
    public function fromArray(array $payload): ReadGoogleSpreadsheetInputDTO
    {
        return new ReadGoogleSpreadsheetInputDTO(
            spreadsheetId: (string) $payload['spreadsheet_id'],
            sheets: $this->normalizeConfiguredSheets($payload['sheets']),
        );
    }

    /**
     * @return array{spreadsheet_id: string, total_sheets: int, total_rows: int, sheets: array<int, array<string, mixed>>}
     */
    public function toArray(ReadGoogleSpreadsheetOutputDTO $dto): array
    {
        return [
            'spreadsheet_id' => $dto->spreadsheetId,
            'total_sheets' => $dto->totalSheets,
            'total_rows' => $dto->totalRows,
            'sheets' => array_map(
                fn (array $sheet): array => $this->normalizeSheet($sheet),
                $dto->sheets,
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $sheet
     * @return array<string, mixed>
     */
    private function normalizeSheet(array $sheet): array
    {
        $data = $sheet['data'] ?? [];

        if ($data instanceof Collection) {
            $data = $data->values()->all();
        }

        return [
            ...$sheet,
            'data' => $data,
        ];
    }

    /**
     * @param  array<int, string|array{name: string}>  $sheets
     * @return array<int, string>
     */
    private function normalizeConfiguredSheets(array $sheets): array
    {
        return collect($sheets)
            ->map(fn (string|array $sheet): string => is_array($sheet) ? (string) $sheet['name'] : $sheet)
            ->all();
    }
}
