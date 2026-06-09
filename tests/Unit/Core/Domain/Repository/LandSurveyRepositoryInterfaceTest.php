<?php

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Domain\Repository\LandSurveyRepositoryInterface;

it('defines the land survey repository contract', function () {
    expect(interface_exists(LandSurveyRepositoryInterface::class))->toBeTrue();

    foreach ([
        'all' => 'array',
        'search' => 'array',
        'findByMunicipality' => 'array',
        'findByProcess' => LandSurveyEntity::class,
        'findByForce' => 'array',
        'findByRegion' => 'array',
        'findByLandStatus' => 'array',
        'findByProgress' => 'array',
    ] as $method => $returnType) {
        $reflection = new ReflectionMethod(LandSurveyRepositoryInterface::class, $method);

        expect($reflection->getReturnType()?->getName())->toBe($returnType);
    }

    expect(LandSurveyRepositoryInterface::class)->toUse([LandSurveyEntity::class]);
});
