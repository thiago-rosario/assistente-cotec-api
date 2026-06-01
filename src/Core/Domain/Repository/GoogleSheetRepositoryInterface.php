<?php

declare(strict_types=1);

namespace App\Core\Domain\Repository;

use App\Core\Domain\Entity\GoogleSheetEntity;
use Illuminate\Support\Collection;

interface GoogleSheetRepositoryInterface
{
    /**
     * @return array{gid: int, sheet: string, total: int, data: Collection<int, array<string, mixed>>}
     */
    public function readSheet(GoogleSheetEntity $sheet): array;

    /**
     * @param  array<int, string>  $sheets
     * @return array<int, array{gid: int, sheet: string, total: int, data: Collection<int, array<string, mixed>>}>
     */
    public function readSpreadsheet(string $spreadsheetId, array $sheets): array;
}
