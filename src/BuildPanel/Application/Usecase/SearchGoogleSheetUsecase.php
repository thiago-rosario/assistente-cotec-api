<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Usecase;

use App\BuildPanel\Application\DTO\SearchGoogleSheetInputDTO;
use App\BuildPanel\Application\DTO\SearchGoogleSheetOutputDTO;
use App\BuildPanel\Application\Interfaces\Usecase\SearchGoogleSheetUsecaseInterface;
use App\BuildPanel\Domain\Entity\GoogleSheetEntity;
use App\BuildPanel\Domain\Repository\GoogleSheetRepositoryInterface;
use App\BuildPanel\Exception\GoogleSheetNotConfiguredException;

class SearchGoogleSheetUsecase implements SearchGoogleSheetUsecaseInterface
{
    public function __construct(
        private readonly GoogleSheetRepositoryInterface $repository,
    ) {}

    public function __invoke(SearchGoogleSheetInputDTO $input): SearchGoogleSheetOutputDTO
    {
        if (! array_key_exists($input->sheetId, $input->sheets)) {
            throw new GoogleSheetNotConfiguredException(sheetId: $input->sheetId);
        }

        $sheet = new GoogleSheetEntity(
            spreadsheetId: $input->spreadsheetId,
            gid: $input->sheetId,
            name: $input->sheets[$input->sheetId],
        );

        $result = $this->repository->searchSheet(
            sheet: $sheet,
            search: $input->search,
        );

        return new SearchGoogleSheetOutputDTO(
            spreadsheetId: $input->spreadsheetId,
            sheetId: $input->sheetId,
            sheet: $result['sheet'],
            search: $input->search,
            total: $result['total'],
            data: $result['data']->values()->all(),
        );
    }
}
