<?php

use App\Core\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\Core\Application\DTO\ReadGoogleSpreadsheetOutputDTO;
use App\Core\Application\Interfaces\ReadGoogleSpreadsheetAdapterInterface;
use App\Core\Domain\Repository\GoogleSheetRepositoryInterface;
use App\Core\Infra\Adapter\ReadGoogleSpreadsheetAdapter;
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

it('resolves the google sheet repository interface to the gateway', function () {
    $repository = app(GoogleSheetRepositoryInterface::class);

    expect($repository)->toBeInstanceOf(GoogleSheetGateway::class);
});
