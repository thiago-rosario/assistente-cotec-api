<?php

use App\Core\Application\DTO\SearchGoogleSheetInputDTO;
use App\Core\Application\DTO\SearchGoogleSheetOutputDTO;
use App\Core\Infra\Adapter\SearchGoogleSheetAdapter;

it('maps google sheet search input arrays into dtos', function () {
    $adapter = new SearchGoogleSheetAdapter;

    $dto = $adapter->fromArray([
        'spreadsheet_id' => 'spreadsheet-id',
        'sheets' => [
            10 => 'Caderno',
        ],
        'sheet_id' => 10,
        'search' => 'salvador',
    ]);

    expect($dto)->toBeInstanceOf(SearchGoogleSheetInputDTO::class)
        ->and($dto->spreadsheetId)->toBe('spreadsheet-id')
        ->and($dto->sheets)->toBe([10 => 'Caderno'])
        ->and($dto->sheetId)->toBe(10)
        ->and($dto->search)->toBe('salvador');
});

it('maps configured sheet arrays by name into google sheet search input dtos', function () {
    $adapter = new SearchGoogleSheetAdapter;

    $dto = $adapter->fromArray([
        'spreadsheet_id' => 'spreadsheet-id',
        'sheets' => [
            10 => ['key' => 'caderno', 'name' => 'Caderno'],
        ],
        'sheet_id' => 10,
        'search' => 'salvador',
    ]);

    expect($dto->sheets)->toBe([10 => 'Caderno']);
});

it('maps google sheet search output dtos into response arrays', function () {
    $adapter = new SearchGoogleSheetAdapter;

    $dto = new SearchGoogleSheetOutputDTO(
        spreadsheetId: 'spreadsheet-id',
        sheetId: 10,
        sheet: 'Caderno',
        search: 'salvador',
        total: 1,
        data: [
            ['municipio' => 'Salvador'],
        ],
    );

    expect($adapter->toArray($dto))->toBe([
        'spreadsheet_id' => 'spreadsheet-id',
        'sheet_id' => 10,
        'sheet' => 'Caderno',
        'search' => 'salvador',
        'total' => 1,
        'data' => [
            ['municipio' => 'Salvador'],
        ],
    ]);
});
