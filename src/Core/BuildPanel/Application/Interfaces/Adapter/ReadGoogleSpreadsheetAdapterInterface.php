<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\Interfaces\Adapter;

use App\Core\BuildPanel\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\Core\BuildPanel\Application\DTO\ReadGoogleSpreadsheetOutputDTO;

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
