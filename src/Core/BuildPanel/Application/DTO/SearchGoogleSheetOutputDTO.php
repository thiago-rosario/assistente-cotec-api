<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\DTO;

readonly class SearchGoogleSheetOutputDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    public function __construct(
        public string $spreadsheetId,
        public int $sheetId,
        public string $sheet,
        public string $search,
        public int $total,
        public array $data,
    ) {}
}
