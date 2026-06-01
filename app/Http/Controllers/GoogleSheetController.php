<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Application\Interfaces\ReadGoogleSpreadsheetAdapterInterface;
use App\Core\Application\Interfaces\ReadGoogleSpreadsheetUsecaseInterface;
use App\Core\Exception\GoogleSheetReadException;
use App\Http\Helper\ResponseJsend;
use Illuminate\Http\JsonResponse;
use Throwable;

class GoogleSheetController extends Controller
{
    public function __construct(
        private readonly ReadGoogleSpreadsheetUsecaseInterface $readGoogleSpreadsheet,
        private readonly ReadGoogleSpreadsheetAdapterInterface $adapter,
    ) {}

    public function __invoke(): JsonResponse
    {
        $spreadsheetId = (string) config('google_sheets.cotec_spreadsheet.spreadsheet_id');
        $configuredSheets = config('google_sheets.cotec_spreadsheet.sheets', []);

        try {
            $input = $this->adapter->fromArray([
                'spreadsheet_id' => $spreadsheetId,
                'sheets' => $configuredSheets,
            ]);

            $output = ($this->readGoogleSpreadsheet)($input);
        } catch (Throwable $throwable) {
            $exception = $throwable instanceof GoogleSheetReadException
                ? $throwable
                : new GoogleSheetReadException(previous: $throwable);
            $reason = $exception->getPrevious() ?? $throwable;

            report($exception);

            return ResponseJsend::error(
                message: $exception->getMessage(),
                code: $exception->getCode(),
                data: [
                    'operation' => 'google_sheet_read',
                    'spreadsheet_id' => $exception->spreadsheetId ?? $spreadsheetId,
                    'sheet' => $exception->sheet,
                    'exception' => $reason::class,
                    'reason' => $reason->getMessage(),
                    'location' => [
                        'file' => $reason->getFile(),
                        'line' => $reason->getLine(),
                    ],
                ],
            )->toJsonResponse(500);
        }

        return ResponseJsend::success($this->adapter->toArray($output))->toJsonResponse();
    }
}
