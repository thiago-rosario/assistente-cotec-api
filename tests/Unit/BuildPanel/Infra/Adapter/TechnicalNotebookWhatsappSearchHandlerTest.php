<?php

use App\BuildPanel\Application\DTO\SearchTechnicalNotebookInputDTO;
use App\BuildPanel\Application\DTO\SearchTechnicalNotebookOutputDTO;
use App\BuildPanel\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\BuildPanel\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;
use App\BuildPanel\Infra\Adapter\TechnicalNotebookWhatsappSearchHandler;

it('searches technical notebooks from whatsapp filters', function () {
    $filters = ['municipality' => 'Antas'];
    $input = new SearchTechnicalNotebookInputDTO(municipality: 'Antas');
    $output = new SearchTechnicalNotebookOutputDTO(
        term: null,
        total: 1,
        data: [
            [
                'municipality' => 'Antas',
                'process' => '020.4487.2021.0009714-69',
            ],
        ],
    );
    $payload = [
        'term' => null,
        'total' => 1,
        'data' => [
            [
                'municipality' => 'Antas',
                'process' => '020.4487.2021.0009714-69',
            ],
        ],
    ];

    $usecase = Mockery::mock(SearchTechnicalNotebookUsecaseInterface::class);
    $usecase->shouldReceive('__invoke')
        ->once()
        ->with($input)
        ->andReturn($output);

    $adapter = Mockery::mock(SearchTechnicalNotebookAdapterInterface::class);
    $adapter->shouldReceive('fromArray')
        ->once()
        ->with($filters)
        ->andReturn($input);
    $adapter->shouldReceive('toArray')
        ->once()
        ->with($output)
        ->andReturn($payload);

    $result = (new TechnicalNotebookWhatsappSearchHandler($usecase, $adapter))->search($filters);

    expect($result)->toBe($payload);
});

it('supports only the technical notebook whatsapp intent', function () {
    $usecase = Mockery::mock(SearchTechnicalNotebookUsecaseInterface::class);
    $adapter = Mockery::mock(SearchTechnicalNotebookAdapterInterface::class);

    $handler = new TechnicalNotebookWhatsappSearchHandler($usecase, $adapter);

    expect($handler->supports('search_technical_notebook'))->toBeTrue()
        ->and($handler->supports('unsupported_intent'))->toBeFalse();
});
