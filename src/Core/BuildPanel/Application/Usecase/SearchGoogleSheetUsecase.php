<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\Usecase;

use App\Core\BuildPanel\Application\DTO\SearchGoogleSheetInputDTO;
use App\Core\BuildPanel\Application\DTO\SearchGoogleSheetOutputDTO;
use App\Core\BuildPanel\Application\Interfaces\Usecase\SearchGoogleSheetUsecaseInterface;
use App\Core\BuildPanel\Domain\Entity\GoogleSheetEntity;
use App\Core\BuildPanel\Domain\Repository\GoogleSheetRepositoryInterface;
use App\Core\BuildPanel\Exception\GoogleSheetNotConfiguredException;

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
