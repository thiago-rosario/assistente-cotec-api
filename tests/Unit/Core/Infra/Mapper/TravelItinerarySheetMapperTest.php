<?php

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Mapper\TravelItinerarySheetMapper;

it('maps a travel itinerary spreadsheet row to the domain entity', function () {
    $entity = (new TravelItinerarySheetMapper)->fromRow([
        'MUNICIPIO' => 'Catu',
        'PROCESSO SEI' => '020.2301.2022.0007756-88',
        'REGIÃO (RISP 2023)' => 'Leste',
        'PLEITO UNIDADE' => 'Delegacia - Reforma 2ª COORPIN',
        'FORÇA' => 'PC',
        'REQUISITANTE' => 'Prefeitura de Catu',
        'SITUAÇÃO DO TERRENO' => 'Aguardando visita técnica.',
        'ANDAMENTO' => 'Aguardando visita técnica.',
        'CONTATO - PONTO FOCAL' => 'Narlison Borges (71) 99681-7358',
        'ROTA' => 'ROTA 01 - OK',
        'LINK MAPA' => 'https://maps.app.goo.gl/Worg597Ru524Y9py9',
    ]);

    expect($entity)->toBeInstanceOf(TravelItineraryEntity::class)
        ->and($entity->municipality)->toBe('Catu')
        ->and($entity->route)->toBe('ROTA 01 - OK')
        ->and($entity->mapLink)->toBe('https://maps.app.goo.gl/Worg597Ru524Y9py9');
});
