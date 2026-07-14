<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\Interfaces\Adapter;

use App\Core\BuildPanel\Application\DTO\SearchGoogleSheetInputDTO;
use App\Core\BuildPanel\Application\DTO\SearchGoogleSheetOutputDTO;

interface SearchGoogleSheetAdapterInterface
{
    /**
     * @param  array{spreadsheet_id: string, sheets: array<int, string|array{name: string}>, sheet_id: int, search: string}  $payload
     */
    public function fromArray(array $payload): SearchGoogleSheetInputDTO;

    /**
     * @return array{spreadsheet_id: string, sheet_id: int, sheet: string, search: string, total: int, data: array<int, array<string, mixed>>}
     */
    public function toArray(SearchGoogleSheetOutputDTO $dto): array;
}
