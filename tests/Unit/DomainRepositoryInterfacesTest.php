<?php

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Domain\Repository\ConstructionDemandRepositoryInterface;
use App\Core\Domain\Repository\LandSurveyRepositoryInterface;
use App\Core\Domain\Repository\NotebookRepositoryInterface;
use App\Core\Domain\Repository\TravelItineraryRepositoryInterface;

it('defines repository contracts for spreadsheet domain entities', function (
    string $repository,
    string $entity,
    array $methods,
) {
    expect(interface_exists($repository))->toBeTrue();

    foreach ($methods as $method => $returnType) {
        $reflection = new ReflectionMethod($repository, $method);

        expect($reflection->getReturnType()?->getName())->toBe($returnType);
    }

    expect($repository)->toUse([$entity]);
})->with([
    'construction demand repository' => [
        ConstructionDemandRepositoryInterface::class,
        ConstructionDemandEntity::class,
        [
            'all' => 'array',
            'search' => 'array',
            'findByMunicipality' => 'array',
            'findByProcess' => ConstructionDemandEntity::class,
            'findByForce' => 'array',
            'findByRegion' => 'array',
            'findByLandStatus' => 'array',
            'findByProgress' => 'array',
        ],
    ],
    'land survey repository' => [
        LandSurveyRepositoryInterface::class,
        LandSurveyEntity::class,
        [
            'all' => 'array',
            'search' => 'array',
            'findByMunicipality' => 'array',
            'findByProcess' => LandSurveyEntity::class,
            'findByForce' => 'array',
            'findByRegion' => 'array',
            'findByLandStatus' => 'array',
            'findByProgress' => 'array',
        ],
    ],
    'notebook repository' => [
        NotebookRepositoryInterface::class,
        NotebookEntity::class,
        [
            'all' => 'array',
            'search' => 'array',
            'findByMunicipality' => 'array',
            'findByRelatedProcess' => NotebookEntity::class,
            'findByRequester' => 'array',
            'findByLandStatus' => 'array',
        ],
    ],
    'travel itinerary repository' => [
        TravelItineraryRepositoryInterface::class,
        TravelItineraryEntity::class,
        [
            'all' => 'array',
            'search' => 'array',
            'findByMunicipality' => 'array',
            'findByProcess' => TravelItineraryEntity::class,
            'findByForce' => 'array',
            'findByRegion' => 'array',
            'findByLandStatus' => 'array',
            'findByProgress' => 'array',
            'findByRequester' => 'array',
        ],
    ],
]);
