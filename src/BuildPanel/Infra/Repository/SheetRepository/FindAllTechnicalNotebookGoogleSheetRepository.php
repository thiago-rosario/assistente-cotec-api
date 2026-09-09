<?php

declare(strict_types=1);

namespace App\BuildPanel\Infra\Repository\SheetRepository;

use App\BuildPanel\Application\Interfaces\Mapper\TechnicalNotebookSheetMapperInterface;
use App\BuildPanel\Domain\Entity\TechnicalNotebookEntity;
use App\BuildPanel\Infra\Trait\HandlesGoogleSheetRows;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Exception\ConnectException;
use Revolution\Google\Sheets\Facades\Sheets;
use Throwable;

class FindAllTechnicalNotebookGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    private const string ReadRange = 'A:ZZ';

    public function __construct(
        private readonly TechnicalNotebookSheetMapperInterface $mapper,
    ) {}

    /**
     * @return list<TechnicalNotebookEntity>
     */
    public function findAllSheet(): array
    {
        $rows = retry(
            [250, 500],
            fn (): mixed => Sheets::spreadsheet($this->spreadsheetId())
                ->sheet($this->sheetName())
                ->range(self::ReadRange)
                ->get(),
            when: fn (Throwable $exception): bool => $this->isTransientGoogleFailure($exception),
        );

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
            ->map(fn (array $row): TechnicalNotebookEntity => $this->mapper->fromRow($row))
            ->values()
            ->all();
    }

    private function isTransientGoogleFailure(Throwable $exception): bool
    {
        if ($exception instanceof ConnectException) {
            return true;
        }

        return $exception instanceof GoogleServiceException
            && in_array($exception->getCode(), [429, 500, 502, 503, 504], true);
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
                || in_array('pleito', $columns, true)
                || in_array('status de obra', $columns, true)
            );
    }
}
