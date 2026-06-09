<?php

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Domain\Repository\ConstructionDemandRepositoryInterface;

it('defines the construction demand repository contract', function () {
    expect(interface_exists(ConstructionDemandRepositoryInterface::class))->toBeTrue();

    foreach ([
        'all' => 'array',
        'search' => 'array',
        'findByMunicipality' => 'array',
        'findByProcess' => ConstructionDemandEntity::class,
        'findByForce' => 'array',
        'findByRegion' => 'array',
        'findByLandStatus' => 'array',
        'findByProgress' => 'array',
    ] as $method => $returnType) {
        $reflection = new ReflectionMethod(ConstructionDemandRepositoryInterface::class, $method);

        expect($reflection->getReturnType()?->getName())->toBe($returnType);
    }

    expect(ConstructionDemandRepositoryInterface::class)->toUse([ConstructionDemandEntity::class]);
});
