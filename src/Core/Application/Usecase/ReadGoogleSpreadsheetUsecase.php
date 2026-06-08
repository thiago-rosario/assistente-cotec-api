<?php

declare(strict_types=1);

namespace App\Core\Application\Usecase;

use App\Core\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\Core\Application\DTO\ReadGoogleSpreadsheetOutputDTO;
use App\Core\Application\Interfaces\Usecase\ReadGoogleSpreadsheetUsecaseInterface;
use App\Core\Domain\Repository\GoogleSheetRepositoryInterface;

class ReadGoogleSpreadsheetUsecase implements ReadGoogleSpreadsheetUsecaseInterface
{
    public function __construct(
        private readonly GoogleSheetRepositoryInterface $repository,
    ) {}

    public function __invoke(ReadGoogleSpreadsheetInputDTO $input): ReadGoogleSpreadsheetOutputDTO
    {
        $sheets = $this->repository->readSpreadsheet(
            spreadsheetId: $input->spreadsheetId,
            sheets: $input->sheets,
        );

        $totalRows = (int) collect($sheets)->sum('total');

        return new ReadGoogleSpreadsheetOutputDTO(
            spreadsheetId: $input->spreadsheetId,
            totalSheets: count($sheets),
            totalRows: $totalRows,
            sheets: $sheets,
        );
    }
}
