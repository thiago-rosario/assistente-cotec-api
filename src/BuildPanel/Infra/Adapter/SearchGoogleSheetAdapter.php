<?php

declare(strict_types=1);

namespace App\BuildPanel\Infra\Adapter;

use App\BuildPanel\Application\DTO\SearchGoogleSheetInputDTO;
use App\BuildPanel\Application\DTO\SearchGoogleSheetOutputDTO;
use App\BuildPanel\Application\Interfaces\Adapter\SearchGoogleSheetAdapterInterface;

class SearchGoogleSheetAdapter implements SearchGoogleSheetAdapterInterface
{
    /**
     * @param  array{spreadsheet_id: string, sheets: array<int, string|array{name: string}>, sheet_id: int, search: string}  $payload
     */
    public function fromArray(array $payload): SearchGoogleSheetInputDTO
    {
        return new SearchGoogleSheetInputDTO(
            spreadsheetId: (string) $payload['spreadsheet_id'],
            sheets: $this->normalizeConfiguredSheets($payload['sheets']),
            sheetId: (int) $payload['sheet_id'],
            search: (string) $payload['search'],
        );
    }

    /**
     * @return array{spreadsheet_id: string, sheet_id: int, sheet: string, search: string, total: int, data: array<int, array<string, mixed>>}
     */
    public function toArray(SearchGoogleSheetOutputDTO $dto): array
    {
        return [
            'spreadsheet_id' => $dto->spreadsheetId,
            'sheet_id' => $dto->sheetId,
            'sheet' => $dto->sheet,
            'search' => $dto->search,
            'total' => $dto->total,
            'data' => $dto->data,
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
