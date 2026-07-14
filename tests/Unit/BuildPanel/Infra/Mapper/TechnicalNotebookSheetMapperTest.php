<?php

use App\Core\BuildPanel\Infra\Mapper\TechnicalNotebookSheetMapper;

it('maps a technical notebook spreadsheet row and casts numeric values', function () {
    $entity = (new TechnicalNotebookSheetMapper)->fromRow([
        'ITEM' => '7',
        'ETAPA' => 'Planejamento',
        'MUNICIPIO' => 'Acajutiba',
        'PROCESSO' => '001.7313.2023.0006626-49',
        'FORÇA' => 'PC',
        'PLEITO' => 'Delegacia',
        'TIPOLOGIA' => '1B',
        'VALOR ESTIMADO' => 'R$ 1.539.740,33',
        'STATUS DE OBRA' => 'Em andamento',
        'DATA DE INAUGURAÇÃO' => '4/30/2023',
    ]);

    expect($entity->item)->toBe(7)
        ->and($entity->estimatedValue)->toBe(1539740.33)
        ->and($entity->municipality)->toBe('Acajutiba')
        ->and($entity->inaugurationDate?->format('Y-m-d'))->toBe('2023-04-30');
});

it('maps technical notebook inauguration dates from the updated sheet', function (string $value, string $expectedDate) {
    $entity = (new TechnicalNotebookSheetMapper)->fromRow([
        'MUNICIPIO' => 'Andaraí',
        'DATA DE INAUGURAÇÃO' => $value,
    ]);

    expect($entity->inaugurationDate?->format('Y-m-d'))->toBe($expectedDate);
})->with([
    'brazilian date' => ['21/06/2024', '2024-06-21'],
    'google us date' => ['10/27/2022', '2022-10-27'],
]);
