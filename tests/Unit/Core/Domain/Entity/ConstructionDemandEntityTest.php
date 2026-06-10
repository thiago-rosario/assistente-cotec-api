<?php

use App\Core\Domain\Entity\ConstructionDemandEntity;

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
        ->and($demand->shouldRequestSoilSurveyAndTopography())->toBeTrue()
        ->and($demand->toSearchableArray())->toContain('Acajutiba', '001.7313.2023.0006626-49', '89122036');
});
