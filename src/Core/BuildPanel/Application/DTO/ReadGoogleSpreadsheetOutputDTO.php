<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\DTO;

readonly class ReadGoogleSpreadsheetOutputDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $sheets
     */
    public function __construct(
        public string $spreadsheetId,
        public int $totalSheets,
        public int $totalRows,
        public array $sheets,
    ) {}
}
