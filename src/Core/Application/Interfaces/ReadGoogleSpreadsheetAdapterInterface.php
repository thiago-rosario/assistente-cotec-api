<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces;

use App\Core\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\Core\Application\DTO\ReadGoogleSpreadsheetOutputDTO;

interface ReadGoogleSpreadsheetAdapterInterface
{
    /**
     * @param  array{spreadsheet_id: string, sheets: array<int, string>}  $payload
     */
    public function fromArray(array $payload): ReadGoogleSpreadsheetInputDTO;

    /**
     * @return array{spreadsheet_id: string, total_sheets: int, total_rows: int, sheets: array<int, array<string, mixed>>}
     */
    public function toArray(ReadGoogleSpreadsheetOutputDTO $dto): array;
}
