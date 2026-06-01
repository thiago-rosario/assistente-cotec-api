<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Application\Interfaces\GoogleSheetRowMapperInterface;
use App\Core\Domain\Entity\GoogleSheetEntity;
use App\Core\Exception\GoogleSheetReadException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Revolution\Google\Sheets\Facades\Sheets;
use Throwable;

class SearchGoogleSheetRepository
{
    private const string ReadRange = 'A:ZZ';

    public function __construct(
        private readonly GoogleSheetRowMapperInterface $mapper,
    ) {}

    /**
     * @return array{gid: int, sheet: string, total: int, data: Collection<int, array<string, mixed>>}
     */
    public function searchSheet(GoogleSheetEntity $sheet, string $search): array
    {
        try {
            $rows = Sheets::spreadsheet($sheet->spreadsheetId)
                ->sheet($sheet->quotedRangeName())
                ->range(self::ReadRange)
                ->get();
        } catch (Throwable $throwable) {
            throw new GoogleSheetReadException(
                previous: $throwable,
                spreadsheetId: $sheet->spreadsheetId,
                sheet: $sheet->toDiagnosticArray(),
            );
        }

        $header = $rows->shift() ?? [];
        $values = $this->mapper->mapRowsToHeader($header, $rows);
        $normalizedSearch = $this->normalize($search);
        $data = $values
            ->filter(fn (array $row): bool => $this->rowContains($row, $normalizedSearch))
            ->values();

        return [
            'gid' => $sheet->gid,
            'sheet' => $sheet->name,
            'total' => $data->count(),
            'data' => $data,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowContains(array $row, string $normalizedSearch): bool
    {
        foreach ($row as $value) {
            if (! is_string($value)) {
                continue;
            }

            if (str_contains($this->normalize($value), $normalizedSearch)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->trim()
            ->lower()
            ->ascii()
            ->toString();
    }
}
