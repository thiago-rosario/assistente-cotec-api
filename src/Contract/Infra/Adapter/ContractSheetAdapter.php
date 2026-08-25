<?php

declare(strict_types=1);

namespace App\Contract\Infra\Adapter;

use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Infra\Exception\ContractSheetRowMappingException;
use App\Core\Application\Interfaces\Mapper\GoogleSheetRowMapperInterface;
use App\Core\Exception\GoogleSheetReadException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Revolution\Google\Sheets\Facades\Sheets;
use Throwable;

class ContractSheetAdapter implements ContractSheetAdapterInterface
{
    public function __construct(
        private readonly GoogleSheetRowMapperInterface $rowMapper,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function read(string $sheetKey): array
    {
        $sheet = config("google_sheets.contract_spreadsheet.sheets.{$sheetKey}");
        $spreadsheetId = (string) config('google_sheets.contract_spreadsheet.spreadsheet_id', '');

        if (! is_array($sheet) || $spreadsheetId === '') {
            throw new ContractSheetRowMappingException(
                message: "A configuração da aba de contratos [{$sheetKey}] não existe.",
                sheet: is_array($sheet) ? ($sheet['name'] ?? null) : null,
            );
        }

        $sheetName = (string) ($sheet['name'] ?? '');
        $range = (string) ($sheet['range'] ?? 'A:Z');
        $headerRow = max(1, (int) ($sheet['header_row'] ?? 1));

        Log::info('contract_sheet_read_started', [
            'sheet' => $sheetName,
            'header_row' => $headerRow,
        ]);

        try {
            $rows = Sheets::spreadsheet($spreadsheetId)
                ->sheet("'{$sheetName}'")
                ->range($range)
                ->get();
        } catch (Throwable $throwable) {
            Log::error('contract_sheet_read_failed', [
                'sheet' => $sheetName,
                'exception' => $throwable::class,
                'exception_message' => $throwable->getMessage(),
            ]);

            throw new GoogleSheetReadException(
                previous: $throwable,
                spreadsheetId: $spreadsheetId,
                sheet: [
                    'gid' => (int) ($sheet['gid'] ?? 0),
                    'name' => $sheetName,
                ],
            );
        }

        try {
            $rows = $rows instanceof Collection ? $rows : collect($rows);
            $header = $rows->get($headerRow - 1, []);

            if (! is_array($header) || $header === []) {
                throw new ContractSheetRowMappingException(
                    message: "A aba de contratos [{$sheetName}] não possui cabeçalho na linha {$headerRow}.",
                    sheet: $sheetName,
                    row: $headerRow,
                );
            }

            $header = array_map(
                static fn (mixed $value): string => trim((string) $value),
                $header,
            );

            $mappedRows = $this->rowMapper->mapRowsToHeader(
                $header,
                $rows->slice($headerRow),
            );

            $data = $mappedRows
                ->filter(static fn (array $row): bool => collect($row)
                    ->contains(static fn (mixed $value): bool => trim((string) $value) !== ''))
                ->values()
                ->all();
        } catch (ContractSheetRowMappingException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            Log::error('contract_sheet_row_mapping_failed', [
                'sheet' => $sheetName,
                'header_row' => $headerRow,
                'exception' => $throwable::class,
                'exception_message' => $throwable->getMessage(),
            ]);

            throw new ContractSheetRowMappingException(
                message: "As linhas da aba de contratos [{$sheetName}] não possuem estrutura compatível.",
                previous: $throwable,
                sheet: $sheetName,
                row: $headerRow,
            );
        }

        Log::info('contract_sheet_read_completed', [
            'sheet' => $sheetName,
            'records' => count($data),
        ]);

        return $data;
    }
}
