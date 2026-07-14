<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\DTO;

readonly class ReadGoogleSpreadsheetInputDTO
{
    /**
     * @param  array<int, string>  $sheets
     */
    public function __construct(
        public string $spreadsheetId,
        public array $sheets,
    ) {}
}
