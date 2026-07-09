<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Adapter;

use App\BuildPanel\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\BuildPanel\Application\DTO\ReadGoogleSpreadsheetOutputDTO;

interface ReadGoogleSpreadsheetAdapterInterface
{
    /**
     * @param  array{spreadsheet_id: string, sheets: array<int, string|array{name: string}>}  $payload
     */
    public function fromArray(array $payload): ReadGoogleSpreadsheetInputDTO;

    /**
     * @return array{spreadsheet_id: string, total_sheets: int, total_rows: int, sheets: array<int, array<string, mixed>>}
     */
    public function toArray(ReadGoogleSpreadsheetOutputDTO $dto): array;
}
