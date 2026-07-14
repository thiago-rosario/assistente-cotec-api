<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\Usecase;

use App\Core\BuildPanel\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\Core\BuildPanel\Application\DTO\ReadGoogleSpreadsheetOutputDTO;
use App\Core\BuildPanel\Application\Interfaces\Usecase\ReadGoogleSpreadsheetUsecaseInterface;
use App\Core\BuildPanel\Domain\Repository\GoogleSheetRepositoryInterface;

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
