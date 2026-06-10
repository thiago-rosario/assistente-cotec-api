<?php

use App\Core\Infra\Mapper\TechnicalNotebookSheetMapper;

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
    ]);

    expect($entity->item)->toBe(7)
        ->and($entity->estimatedValue)->toBe(1539740.33)
        ->and($entity->municipality)->toBe('Acajutiba');
});
