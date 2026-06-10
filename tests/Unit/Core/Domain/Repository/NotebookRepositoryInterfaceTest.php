<?php

use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Domain\Repository\NotebookRepositoryInterface;

it('defines the notebook repository contract', function () {
    expect(interface_exists(NotebookRepositoryInterface::class))->toBeTrue();

    foreach ([
        'all' => 'array',
        'search' => 'array',
        'findByMunicipality' => 'array',
        'findByRelatedProcess' => NotebookEntity::class,
        'findByRequester' => 'array',
        'findByLandStatus' => 'array',
    ] as $method => $returnType) {
        $reflection = new ReflectionMethod(NotebookRepositoryInterface::class, $method);

        expect($reflection->getReturnType()?->getName())->toBe($returnType);
    }

    expect(NotebookRepositoryInterface::class)->toUse([NotebookEntity::class]);
});
