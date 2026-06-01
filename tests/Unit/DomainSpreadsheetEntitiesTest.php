<?php

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Domain\Resolver\UnitSizeResolver;

it('represents a notebook row from the caderno sheet', function () {
    $notebook = new NotebookEntity(
        municipality: 'ACAJUTIBA',
        relatedProcess: '001.7313.2023.0006626-49',
        unitClaim: 'Delegacia',
        objectSize: 'a preencher',
        landStatus: 'Terreno Doado (terreno aprovado pela equipe técnica da SSP/CEIRF)',
        requester: 'Prefeitura',
        estimatedCost: 1539740.33,
    );

    expect($notebook->hasRelatedProcess())->toBeTrue()
        ->and($notebook->hasEstimatedCost())->toBeTrue()
        ->and($notebook->hasDefinedObjectSize())->toBeFalse();
});

it('represents a travel itinerary row from the rotas sheet', function () {
    $itinerary = new TravelItineraryEntity(
        municipality: 'Catu',
        process: '020.2301.2022.0007756-88',
        region: 'Leste',
        unitClaim: 'Delegacia - Reforma 2ª COORPIN',
        force: 'PC',
        requester: 'Prefeitura de Catu',
        landStatus: 'Aguardando visita técnica.',
        progress: 'Aguardando visita técnica.',
        focalPointContact: 'Narlison Borges (Prefeito) (71) 99681-7358',
        route: 'ROTA 01 - OK',
        mapLink: 'https://maps.app.goo.gl/Worg597Ru524Y9py9',
    );

    expect($itinerary->hasMapLink())->toBeTrue()
        ->and($itinerary->hasFocalPointContact())->toBeTrue()
        ->and($itinerary->awaitsTechnicalVisit())->toBeTrue();
});

it('represents a construction demand row from the demanda de construcao sheet', function () {
    $demand = new ConstructionDemandEntity(
        municipality: 'Acajutiba',
        force: 'PC',
        process: '001.7313.2023.0006626-49',
        unitClaim: 'Delegacia',
        requesterDescription: 'Prefeito Alexsandro Menezes (75) 99819-5058',
        landStatus: 'Terreno doado',
        progress: 'Terreno vistoriado e aprovado pela CEIRF',
        inspectionReport: '89122036',
        unitSizeClaim: '1B',
        region: 'Leste',
        requester: 'Prefeitura',
        soilSurveyAndTopography: 'solicitar',
    );

    expect($demand->hasProcess())->toBeTrue()
        ->and($demand->hasInspectionReport())->toBeTrue()
        ->and($demand->shouldRequestSoilSurveyAndTopography())->toBeTrue();
});

it('represents a land survey row from the backup sheet', function () {
    $survey = new LandSurveyEntity(
        municipality: 'Alcobaça',
        process: '030.2709.2022.0197573-43',
        region: 'Sul',
        unitSizeClaim: 'CIPM',
        force: 'PM',
        requester: 'Comando Região Sul',
        ownership: null,
        topography: 'Levantamento recebido',
        landStatus: 'Aguardando visita técnica',
        progress: 'Entrar em contato com prepostos da Prefeitura.',
        municipalityFocalPointContact: 'Prefeito Givaldo Muniz (73) 99926-2900',
        militaryPoliceFocalPointContact: 'Maj Marion (71)9959-6811',
        civilPoliceFocalPointContact: null,
        documentationLink: 'https://example.com/documentacao',
        updatedAt: new DateTimeImmutable('2026-05-01'),
        observations: 'Doado',
        requestedAt: new DateTimeImmutable('2019-03-20'),
    );

    expect($survey->hasDocumentationLink())->toBeTrue()
        ->and($survey->hasPoliceFocalPointContact())->toBeTrue()
        ->and($survey->hasTopography())->toBeTrue();
});

it('resolves unit sizes from the tamanhos sheet values', function () {
    $resolver = new UnitSizeResolver;

    expect($resolver->findByCode('1B PC'))->toMatchArray([
        'code' => '1B PM',
        'standard_size' => '38 x22',
        'standard_area' => '850m²',
    ])
        ->and($resolver->findByCode('Central de Custódia DPT'))->toMatchArray([
            'standard_size' => '23,8 x 8,6',
            'standard_area' => '205 m²',
        ])
        ->and($resolver->search('conjugada'))->toHaveCount(2);
});
