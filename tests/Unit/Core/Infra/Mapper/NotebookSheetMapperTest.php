<?php

use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Infra\Mapper\NotebookSheetMapper;

it('maps a notebook spreadsheet row and casts estimated cost', function () {
    $entity = (new NotebookSheetMapper)->fromRow([
        'MUNICIPIO' => ' Acajutiba ',
        'Processo SEI relacionado' => '001.7313.2023.0006626-49',
        'Pleito da Unidade' => 'Delegacia',
        'TAMANHO DO OBJETO' => '1B',
        'Situação do terreno' => 'Terreno doado',
        'Solicitante' => 'Prefeitura',
        'Custo Estimado' => 'R$ 1.539.740,33',
    ]);

    expect($entity)->toBeInstanceOf(NotebookEntity::class)
        ->and($entity->municipality)->toBe('Acajutiba')
        ->and($entity->relatedProcess)->toBe('001.7313.2023.0006626-49')
        ->and($entity->estimatedCost)->toBe(1539740.33);
});
