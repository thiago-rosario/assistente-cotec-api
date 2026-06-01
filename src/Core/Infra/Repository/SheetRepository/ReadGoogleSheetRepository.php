<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Application\Interfaces\GoogleSheetRowMapperInterface;
use App\Core\Domain\Entity\GoogleSheetEntity;
use Revolution\Google\Sheets\Facades\Sheets;

class ReadGoogleSheetRepository
{
    private const string ReadRange = 'A:ZZ';

    public function __construct(
        private readonly GoogleSheetRowMapperInterface $mapper,
    ) {}

    public function readSheet(GoogleSheetEntity $sheet): array
    {
        $rows = Sheets::spreadsheet($sheet->spreadsheetId)
            ->sheet($sheet->quotedRangeName())
            ->range(self::ReadRange)
            ->get();

        $header = $rows->shift() ?? [];
        $values = $this->mapper->mapRowsToHeader($header, $rows);

        return [
            'gid' => $sheet->gid,
            'sheet' => $sheet->name,
            'total' => $values->count(),
            'data' => $values->values(),
        ];
    }
}
