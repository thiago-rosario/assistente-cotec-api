<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\DTO;

readonly class SearchGoogleSheetInputDTO
{
    /**
     * @param  array<int, string>  $sheets
     */
    public function __construct(
        public string $spreadsheetId,
        public array $sheets,
        public int $sheetId,
        public string $search,
    ) {}
}
