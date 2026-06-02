<?php

use App\Core\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\Core\Application\DTO\ReadGoogleSpreadsheetOutputDTO;
use App\Core\Application\DTO\SearchGoogleSheetInputDTO;
use App\Core\Application\DTO\SearchGoogleSheetOutputDTO;
use App\Core\Application\Interfaces\ReadGoogleSpreadsheetAdapterInterface;
use App\Core\Application\Interfaces\SearchConstructionDemandUsecaseInterface;
use App\Core\Application\Interfaces\SearchGoogleSheetAdapterInterface;
use App\Core\Application\Interfaces\SearchLandSurveyUsecaseInterface;
use App\Core\Application\Interfaces\SearchTravelItineraryUsecaseInterface;
use App\Core\Domain\Repository\GoogleSheetRepositoryInterface;
use App\Core\Infra\Adapter\ReadGoogleSpreadsheetAdapter;
use App\Core\Infra\Adapter\SearchGoogleSheetAdapter;
use App\Core\Infra\Repository\Gateway\GoogleSheetGateway;
use Tests\TestCase;

uses(TestCase::class);

it('maps google spreadsheet input arrays into dtos', function () {
    $adapter = new ReadGoogleSpreadsheetAdapter;

    $dto = $adapter->fromArray([
        'spreadsheet_id' => 'spreadsheet-id',
        'sheets' => [
            10 => 'Caderno',
            20 => 'Rotas',
        ],
    ]);

    expect($dto)->toBeInstanceOf(ReadGoogleSpreadsheetInputDTO::class)
        ->and($dto->spreadsheetId)->toBe('spreadsheet-id')
        ->and($dto->sheets)->toBe([
            10 => 'Caderno',
            20 => 'Rotas',
        ]);
});

it('maps configured sheet arrays by name into google spreadsheet input dtos', function () {
    $adapter = new ReadGoogleSpreadsheetAdapter;

    $dto = $adapter->fromArray([
        'spreadsheet_id' => 'spreadsheet-id',
        'sheets' => [
            10 => ['key' => 'caderno', 'name' => 'Caderno'],
            20 => ['key' => 'rotas', 'name' => 'Rotas'],
        ],
    ]);

    expect($dto->sheets)->toBe([
        10 => 'Caderno',
        20 => 'Rotas',
    ]);
});

it('maps google spreadsheet output dtos into response arrays', function () {
    $adapter = new ReadGoogleSpreadsheetAdapter;

    $dto = new ReadGoogleSpreadsheetOutputDTO(
        spreadsheetId: 'spreadsheet-id',
        totalSheets: 1,
        totalRows: 1,
        sheets: [
            [
                'gid' => 10,
                'sheet' => 'Caderno',
                'total' => 1,
                'data' => collect([
                    ['nome' => 'Maria'],
                ]),
            ],
        ],
    );

    $payload = $adapter->toArray($dto);

    expect($payload)->toBe([
        'spreadsheet_id' => 'spreadsheet-id',
        'total_sheets' => 1,
        'total_rows' => 1,
        'sheets' => [
            [
                'gid' => 10,
                'sheet' => 'Caderno',
                'total' => 1,
                'data' => [
                    ['nome' => 'Maria'],
                ],
            ],
        ],
    ]);
});

it('resolves the google spreadsheet adapter interface from the container', function () {
    $adapter = app(ReadGoogleSpreadsheetAdapterInterface::class);

    expect($adapter)->toBeInstanceOf(ReadGoogleSpreadsheetAdapter::class);
});

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

it('resolves the google sheet search adapter interface from the container', function () {
    $adapter = app(SearchGoogleSheetAdapterInterface::class);

    expect($adapter)->toBeInstanceOf(SearchGoogleSheetAdapter::class);
});

it('resolves the google sheet repository interface to the gateway', function () {
    $repository = app(GoogleSheetRepositoryInterface::class);

    expect($repository)->toBeInstanceOf(GoogleSheetGateway::class);
});

it('binds the spreadsheet domain search usecase interfaces', function () {
    expect(app()->bound(SearchConstructionDemandUsecaseInterface::class))->toBeTrue()
        ->and(app()->bound(SearchLandSurveyUsecaseInterface::class))->toBeTrue()
        ->and(app()->bound(SearchTravelItineraryUsecaseInterface::class))->toBeTrue();
});
