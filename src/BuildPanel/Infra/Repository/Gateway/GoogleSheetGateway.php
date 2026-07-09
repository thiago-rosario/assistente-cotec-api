<?php

declare(strict_types=1);

namespace App\BuildPanel\Infra\Repository\Gateway;

use App\BuildPanel\Domain\Entity\GoogleSheetEntity;
use App\BuildPanel\Domain\Repository\GoogleSheetRepositoryInterface;
use App\BuildPanel\Infra\Repository\SheetRepository\ReadGoogleSheetRepository;
use App\BuildPanel\Infra\Repository\SheetRepository\ReadGoogleSpreadsheetRepository;
use App\BuildPanel\Infra\Repository\SheetRepository\SearchGoogleSheetRepository;

final readonly class GoogleSheetGateway implements GoogleSheetRepositoryInterface
{
    public function __construct(
        private ReadGoogleSheetRepository $readGoogleSheetRepository,
        private ReadGoogleSpreadsheetRepository $readGoogleSpreadsheetRepository,
        private SearchGoogleSheetRepository $searchGoogleSheetRepository,
    ) {}

    public function readSheet(GoogleSheetEntity $sheet): array
    {
        return $this->readGoogleSheetRepository->readSheet($sheet);
    }

    public function searchSheet(GoogleSheetEntity $sheet, string $search): array
    {
        return $this->searchGoogleSheetRepository->searchSheet($sheet, $search);
    }

    public function readSpreadsheet(string $spreadsheetId, array $sheets): array
    {
        return $this->readGoogleSpreadsheetRepository->readSpreadsheet($spreadsheetId, $sheets);
    }
}
