<?php

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Infra\Mapper\ConstructionDemandSheetMapper;

it('maps a construction demand spreadsheet row to the domain entity', function () {
    $entity = (new ConstructionDemandSheetMapper)->fromRow([
        'MUNICIPIO' => ' Acajutiba ',
        'FORÇA' => 'PC',
        'PROCESSO SEI' => '001.7313.2023.0006626-49',
        'Pleito da Unidadade' => 'Delegacia',
        'Solicitante' => 'Prefeito Alexsandro Menezes',
        'SITUAÇÃO DO TERRENO' => 'Terreno doado',
        'ANDAMENTO' => 'Terreno vistoriado',
        'RELATÓRIO VISTORIA' => '89122036',
        'PLEITO UNIDADE TAMANHO' => '1B',
        'REGIÃO (RISP 2023)' => 'Leste',
        'SONDAGEM E TOPOGRAFIA' => 'solicitar',
    ]);

    expect($entity)->toBeInstanceOf(ConstructionDemandEntity::class)
        ->and($entity->municipality)->toBe('Acajutiba')
        ->and($entity->process)->toBe('001.7313.2023.0006626-49')
        ->and($entity->unitClaim)->toBe('Delegacia')
        ->and($entity->inspectionReport)->toBe('89122036')
        ->and($entity->unitSizeClaim)->toBe('1B')
        ->and($entity->region)->toBe('Leste')
        ->and($entity->soilSurveyAndTopography)->toBe('solicitar');
});
