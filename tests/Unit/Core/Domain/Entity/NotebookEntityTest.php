<?php

use App\Core\Domain\Entity\NotebookEntity;

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
        ->and($notebook->hasDefinedObjectSize())->toBeFalse()
        ->and($notebook->toSearchableArray())->toContain('ACAJUTIBA', '001.7313.2023.0006626-49', 'Delegacia');
});
