<?php

use App\Core\BuildPanel\Application\DTO\SearchTechnicalNotebookInputDTO;
use App\Core\BuildPanel\Application\DTO\SearchTechnicalNotebookOutputDTO;
use App\Core\BuildPanel\Infra\Adapter\SearchTechnicalNotebookAdapter;

it('maps technical notebook search input arrays into dtos', function () {
    $adapter = new SearchTechnicalNotebookAdapter;

    $dto = $adapter->fromArray([
        'process' => '12345',
        'municipality' => 'Salvador',
        'force' => 'Judicial',
        'build_status' => 'Finalizado',
        'term' => 'escola',
    ]);

    expect($dto)->toBeInstanceOf(SearchTechnicalNotebookInputDTO::class)
        ->and($dto->process)->toBe('12345')
        ->and($dto->municipality)->toBe('Salvador')
        ->and($dto->force)->toBe('Judicial')
        ->and($dto->buildStatus)->toBe('Finalizado')
        ->and($dto->term)->toBe('escola');
});

it('maps technical notebook search output dtos into response arrays', function () {
    $adapter = new SearchTechnicalNotebookAdapter;

    $dto = new SearchTechnicalNotebookOutputDTO(
        term: 'escola',
        total: 1,
        data: [
            ['municipality' => 'Salvador'],
        ],
    );

    expect($adapter->toArray($dto))->toBe([
        'term' => 'escola',
        'total' => 1,
        'data' => [
            ['municipality' => 'Salvador'],
        ],
    ]);
});
