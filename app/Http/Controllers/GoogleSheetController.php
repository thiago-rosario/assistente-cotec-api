<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Exception\GoogleSheetReadException;
use App\Http\Helper\ResponseJsend;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Revolution\Google\Sheets\Facades\Sheets;
use Throwable;

class GoogleSheetController extends Controller
{
    private const SpreadsheetId = '1pcjdC19nNJAPKIYCirgwIBZIJsBrcFuCTpDEOUbpPOw';

    /**
     * @var array<int, string>
     */
    private const Sheets = [
        615480757 => 'DEMANDA DE CONSTRUÇÃO',
        1355441995 => 'Caderno',
        1142334527 => 'ROTAS',
        1964615295 => 'Reformas',
        941971074 => 'TAMANHOS',
        1426277740 => 'PESQUISA',
        1843958344 => 'CADERNO TÉCNICO',
    ];

    public function __invoke(): JsonResponse
    {
        $currentSheet = null;

        try {
            $sheets = collect(self::Sheets)->map(function (string $sheetName, int $gid) use (&$currentSheet): array {
                $currentSheet = [
                    'gid' => $gid,
                    'name' => $sheetName,
                ];

                $rows = Sheets::spreadsheet(self::SpreadsheetId)
                    ->sheet($this->formatSheetRange($sheetName))
                    ->range('A:ZZ')
                    ->get();

                $header = $rows->shift() ?? [];
                $values = $this->mapRowsToHeader($header, $rows);

                return [
                    'gid' => $gid,
                    'sheet' => $sheetName,
                    'total' => $values->count(),
                    'data' => $values->values(),
                ];
            })->values();
        } catch (Throwable $throwable) {
            $exception = new GoogleSheetReadException(previous: $throwable);

            report($exception);

            return ResponseJsend::error(
                message: $exception->getMessage(),
                code: $exception->getCode(),
                data: [
                    'operation' => 'google_sheet_read',
                    'spreadsheet_id' => self::SpreadsheetId,
                    'sheet' => $currentSheet,
                    'exception' => $throwable::class,
                    'reason' => $throwable->getMessage(),
                    'location' => [
                        'file' => $throwable->getFile(),
                        'line' => $throwable->getLine(),
                    ],
                ],
            )->toJsonResponse(500);
        }

        return ResponseJsend::success([
            'spreadsheet_id' => self::SpreadsheetId,
            'total_sheets' => $sheets->count(),
            'total_rows' => $sheets->sum('total'),
            'sheets' => $sheets,
        ])->toJsonResponse();
    }

    /**
     * @param  array<int, string>  $header
     * @param  Collection<int, array<int, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function mapRowsToHeader(array $header, Collection $rows): Collection
    {
        if ($header === []) {
            return collect();
        }

        return $rows->map(function (array $row) use ($header): array {
            return collect($header)
                ->combine(collect($row)->take(count($header))->pad(count($header), ''))
                ->all();
        });
    }

    private function formatSheetRange(string $sheetName): string
    {
        return "'".str_replace("'", "''", $sheetName)."'";
    }
}
