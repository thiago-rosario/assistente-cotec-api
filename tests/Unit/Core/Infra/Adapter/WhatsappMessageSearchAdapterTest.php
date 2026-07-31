<?php

use App\BuildPanel\Application\DTO\SearchTechnicalNotebookInputDTO;
use App\BuildPanel\Application\DTO\SearchTechnicalNotebookOutputDTO;
use App\BuildPanel\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\BuildPanel\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;
use App\BuildPanel\Infra\Adapter\WhatsappMessageSearchAdapter;

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

    $result = (new WhatsappMessageSearchAdapter($usecase, $adapter))->search(
        intent: 'search_technical_notebook',
        filters: $filters,
    );

    expect($result)->toBe($payload);
});

it('returns an empty result for unsupported whatsapp search intents', function () {
    $usecase = Mockery::mock(SearchTechnicalNotebookUsecaseInterface::class);
    $usecase->shouldReceive('__invoke')->never();

    $adapter = Mockery::mock(SearchTechnicalNotebookAdapterInterface::class);
    $adapter->shouldReceive('fromArray')->never();
    $adapter->shouldReceive('toArray')->never();

    $result = (new WhatsappMessageSearchAdapter($usecase, $adapter))->search(
        intent: 'unsupported_intent',
        filters: ['municipality' => 'Antas'],
    );

    expect($result)->toBe([
        'term' => null,
        'total' => 0,
        'data' => [],
    ]);
});
