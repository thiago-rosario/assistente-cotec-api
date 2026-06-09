<?php

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Domain\Repository\TravelItineraryRepositoryInterface;

it('defines the travel itinerary repository contract', function () {
    expect(interface_exists(TravelItineraryRepositoryInterface::class))->toBeTrue();

    foreach ([
        'all' => 'array',
        'search' => 'array',
        'findByMunicipality' => 'array',
        'findByProcess' => TravelItineraryEntity::class,
        'findByForce' => 'array',
        'findByRegion' => 'array',
        'findByLandStatus' => 'array',
        'findByProgress' => 'array',
        'findByRequester' => 'array',
    ] as $method => $returnType) {
        $reflection = new ReflectionMethod(TravelItineraryRepositoryInterface::class, $method);

        expect($reflection->getReturnType()?->getName())->toBe($returnType);
    }

    expect(TravelItineraryRepositoryInterface::class)->toUse([TravelItineraryEntity::class]);
});
