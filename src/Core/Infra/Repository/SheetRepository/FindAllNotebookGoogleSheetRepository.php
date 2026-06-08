<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Application\Interfaces\Mapper\NotebookSheetMapperInterface;
use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;
use Revolution\Google\Sheets\Facades\Sheets;

class FindAllNotebookGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    private const string ReadRange = 'A:ZZ';

    public function __construct(
        private readonly NotebookSheetMapperInterface $mapper,
    ) {}

    /**
     * @return list<NotebookEntity>
     */
    public function findAllSheet(): array
    {
        $rows = Sheets::spreadsheet($this->spreadsheetId())
            ->sheet($this->sheetName())
            ->range(self::ReadRange)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $header = $this->toArray($rows->shift() ?? []);

        return $rows
            ->map(fn (mixed $row): array => $this->combineHeader($header, $this->toArray($row)))
            ->filter(fn (array $row): bool => $this->hasUsefulData($row))
            ->map(fn (array $row): NotebookEntity => $this->mapper->fromRow($row))
            ->values()
            ->all();
    }

    /**
     * @return array<int, mixed>
     */
    private function toArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        return [];
    }

    private function sheetName(): string
    {
        return (string) config('google_sheets.cotec_spreadsheet.sheets.1355441995.name');
    }
}
