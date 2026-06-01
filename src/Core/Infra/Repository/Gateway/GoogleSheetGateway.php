<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\Gateway;

use App\Core\Domain\Entity\GoogleSheetEntity;
use App\Core\Domain\Repository\GoogleSheetRepositoryInterface;
use App\Core\Infra\Repository\SheetRepository\ReadGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\ReadGoogleSpreadsheetRepository;
use App\Core\Infra\Repository\SheetRepository\SearchGoogleSheetRepository;

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
