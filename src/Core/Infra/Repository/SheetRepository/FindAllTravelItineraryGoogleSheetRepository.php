<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Application\Interfaces\Mapper\TravelItinerarySheetMapperInterface;
use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;
use Revolution\Google\Sheets\Facades\Sheets;

class FindAllTravelItineraryGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    private const string ReadRange = 'A:ZZ';

    public function __construct(
        private readonly TravelItinerarySheetMapperInterface $mapper,
    ) {}

    /**
     * @return list<TravelItineraryEntity>
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

        $rows = $rows
            ->map(fn (mixed $row): array => $this->toArray($row))
            ->values();

        $headerIndex = $rows->search(fn (array $row): bool => $this->isHeaderRow($row));

        if ($headerIndex === false) {
            return [];
        }

        $header = $rows->get($headerIndex);

        return $rows
            ->slice($headerIndex + 1)
            ->map(fn (mixed $row): array => $this->combineHeader($header, $this->toArray($row)))
            ->filter(fn (array $row): bool => $this->hasUsefulData($row))
            ->map(fn (array $row): TravelItineraryEntity => $this->mapper->fromRow($row))
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

    /**
     * @param  array<int, mixed>  $row
     */
    private function isHeaderRow(array $row): bool
    {
        $columns = array_map(fn (mixed $value): string => $this->normalize((string) $value), $row);

        return in_array('municipio', $columns, true)
            && (
                in_array('processo', $columns, true)
                || in_array('processo sei', $columns, true)
            );
    }

    private function sheetName(): string
    {
        return (string) config('google_sheets.cotec_spreadsheet.sheets.1142334527.name');
    }
}
