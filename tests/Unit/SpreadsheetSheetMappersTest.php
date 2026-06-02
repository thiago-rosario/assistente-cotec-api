<?php

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Mapper\ConstructionDemandSheetMapper;
use App\Core\Infra\Mapper\LandSurveySheetMapper;
use App\Core\Infra\Mapper\NotebookSheetMapper;
use App\Core\Infra\Mapper\TechnicalNotebookSheetMapper;
use App\Core\Infra\Mapper\TravelItinerarySheetMapper;

it('maps a construction demand spreadsheet row to the domain entity', function () {
    $entity = (new ConstructionDemandSheetMapper)->fromRow([
        'MUNICIPIO' => ' Acajutiba ',
        'FORÇA' => 'PC',
        'PROCESSO' => '001.7313.2023.0006626-49',
        'PLEITO' => 'Delegacia',
        'DESCRIÇÃO DO SOLICITANTE' => 'Prefeito Alexsandro Menezes',
        'STATUS DO TERRENO' => 'Terreno doado',
        'ANDAMENTO' => 'Terreno vistoriado',
        'RELATÓRIO DE VISTORIA' => '89122036',
        'TIPOLOGIA' => '1B',
        'REGIÃO' => 'Leste',
        'SOLICITANTE' => 'Prefeitura',
        'SONDAGEM E TOPOGRAFIA' => 'solicitar',
    ]);

    expect($entity)->toBeInstanceOf(ConstructionDemandEntity::class)
        ->and($entity->municipality)->toBe('Acajutiba')
        ->and($entity->process)->toBe('001.7313.2023.0006626-49')
        ->and($entity->soilSurveyAndTopography)->toBe('solicitar');
});

it('maps a land survey spreadsheet row to the domain entity', function () {
    $entity = (new LandSurveySheetMapper)->fromRow([
        'MUNICIPIO' => 'Alcobaça',
        'PROCESSO' => '030.2709.2022.0197573-43',
        'REGIÃO' => 'Sul',
        'PLEITO' => 'CIPM',
        'FORÇA' => 'PM',
        'SOLICITANTE' => 'Comando Região Sul',
        'TITULARIDADE' => '',
        'TOPOGRAFIA' => 'Levantamento recebido',
        'STATUS DO TERRENO' => 'Aguardando visita técnica',
        'ANDAMENTO' => 'Entrar em contato com prepostos da Prefeitura.',
        'CONTATO MUNICÍPIO' => 'Prefeito Givaldo Muniz (73) 99926-2900',
        'CONTATO PM' => 'Maj Marion (71)9959-6811',
        'CONTATO PC' => '',
        'LINK DOCUMENTAÇÃO' => 'https://example.com/documentacao',
        'ATUALIZADO EM' => '01/05/2026',
        'OBSERVAÇÕES' => 'Doado',
        'SOLICITADO EM' => '20/03/2019',
    ]);

    expect($entity)->toBeInstanceOf(LandSurveyEntity::class)
        ->and($entity->municipality)->toBe('Alcobaça')
        ->and($entity->ownership)->toBeNull()
        ->and($entity->updatedAt?->format('Y-m-d'))->toBe('2026-05-01')
        ->and($entity->requestedAt?->format('Y-m-d'))->toBe('2019-03-20');
});

it('maps a travel itinerary spreadsheet row to the domain entity', function () {
    $entity = (new TravelItinerarySheetMapper)->fromRow([
        'MUNICIPIO' => 'Catu',
        'PROCESSO' => '020.2301.2022.0007756-88',
        'REGIÃO' => 'Leste',
        'PLEITO' => 'Delegacia - Reforma 2ª COORPIN',
        'FORÇA' => 'PC',
        'SOLICITANTE' => 'Prefeitura de Catu',
        'STATUS DO TERRENO' => 'Aguardando visita técnica.',
        'ANDAMENTO' => 'Aguardando visita técnica.',
        'CONTATO PONTO FOCAL' => 'Narlison Borges (71) 99681-7358',
        'ROTA' => 'ROTA 01 - OK',
        'LINK MAPA' => 'https://maps.app.goo.gl/Worg597Ru524Y9py9',
    ]);

    expect($entity)->toBeInstanceOf(TravelItineraryEntity::class)
        ->and($entity->municipality)->toBe('Catu')
        ->and($entity->route)->toBe('ROTA 01 - OK')
        ->and($entity->mapLink)->toBe('https://maps.app.goo.gl/Worg597Ru524Y9py9');
});

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

it('maps a notebook spreadsheet row and casts estimated cost', function () {
    $entity = (new NotebookSheetMapper)->fromRow([
        'MUNICIPIO' => ' Acajutiba ',
        'PROCESSO RELACIONADO' => '001.7313.2023.0006626-49',
        'PLEITO' => 'Delegacia',
        'TAMANHO DO OBJETO' => '1B',
        'STATUS DO TERRENO' => 'Terreno doado',
        'SOLICITANTE' => 'Prefeitura',
        'CUSTO ESTIMADO' => 'R$ 1.539.740,33',
    ]);

    expect($entity)->toBeInstanceOf(NotebookEntity::class)
        ->and($entity->municipality)->toBe('Acajutiba')
        ->and($entity->relatedProcess)->toBe('001.7313.2023.0006626-49')
        ->and($entity->estimatedCost)->toBe(1539740.33);
});
