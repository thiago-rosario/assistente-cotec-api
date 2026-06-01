<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\Gateway;

use App\Core\Domain\Entity\GoogleSheetEntity;
use App\Core\Domain\Repository\GoogleSheetRepositoryInterface;
use App\Core\Infra\Repository\SheetRepository\ReadGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\ReadGoogleSpreadsheetRepository;

final readonly class GoogleSheetGateway implements GoogleSheetRepositoryInterface
{
    public function __construct(
        private ReadGoogleSheetRepository $readGoogleSheetRepository,
        private ReadGoogleSpreadsheetRepository $readGoogleSpreadsheetRepository,
    ) {}

    public function readSheet(GoogleSheetEntity $sheet): array
    {
        return $this->readGoogleSheetRepository->readSheet($sheet);
    }

    public function readSpreadsheet(string $spreadsheetId, array $sheets): array
    {
        return $this->readGoogleSpreadsheetRepository->readSpreadsheet($spreadsheetId, $sheets);
    }
}
