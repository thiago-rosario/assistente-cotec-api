<?php

declare(strict_types=1);

namespace App\BuildPanel\Infra\Repository\SheetRepository;

use App\BuildPanel\Domain\Entity\GoogleSheetEntity;
use App\BuildPanel\Exception\GoogleSheetReadException;
use Throwable;

class ReadGoogleSpreadsheetRepository
{
    public function __construct(
        private readonly ReadGoogleSheetRepository $readGoogleSheetRepository,
    ) {}

    /**
     * @param  array<int, string>  $sheets
     * @return array<int, array<string, mixed>>
     */
    public function readSpreadsheet(string $spreadsheetId, array $sheets): array
    {
        return collect(GoogleSheetEntity::fromConfiguredSheets($spreadsheetId, $sheets))
            ->map(function (GoogleSheetEntity $sheet) use ($spreadsheetId): array {
                try {
                    return $this->readGoogleSheetRepository->readSheet($sheet);
                } catch (Throwable $throwable) {
                    throw new GoogleSheetReadException(
                        previous: $throwable,
                        spreadsheetId: $spreadsheetId,
                        sheet: $sheet->toDiagnosticArray(),
                    );
                }
            })
            ->values()
            ->all();
    }
}
