<?php

use App\Core\Domain\Entity\TravelItineraryEntity;

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
        ->and($itinerary->awaitsTechnicalVisit())->toBeTrue()
        ->and($itinerary->toSearchableArray())->toContain('Catu', '020.2301.2022.0007756-88', 'ROTA 01 - OK');
});
