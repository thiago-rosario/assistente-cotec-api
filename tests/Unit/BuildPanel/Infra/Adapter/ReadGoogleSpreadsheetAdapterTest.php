<?php

use App\Core\BuildPanel\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\Core\BuildPanel\Application\DTO\ReadGoogleSpreadsheetOutputDTO;
use App\Core\BuildPanel\Infra\Adapter\ReadGoogleSpreadsheetAdapter;

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
