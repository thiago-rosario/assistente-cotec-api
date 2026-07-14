<?php

use App\Core\Conversation\Application\Interfaces\Service\WhatsappMessageSearchHandlerInterface;
use App\Core\Conversation\Infra\Adapter\WhatsappMessageSearchAdapter;

it('delegates whatsapp searches to the handler that supports the intent', function () {
    $filters = ['municipality' => 'Antas'];
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

    $unsupportedHandler = Mockery::mock(WhatsappMessageSearchHandlerInterface::class);
    $unsupportedHandler->shouldReceive('supports')
        ->once()
        ->with('search_technical_notebook')
        ->andReturnFalse();
    $unsupportedHandler->shouldReceive('search')->never();

    $supportedHandler = Mockery::mock(WhatsappMessageSearchHandlerInterface::class);
    $supportedHandler->shouldReceive('supports')
        ->once()
        ->with('search_technical_notebook')
        ->andReturnTrue();
    $supportedHandler->shouldReceive('search')
        ->once()
        ->with($filters)
        ->andReturn($payload);

    $result = (new WhatsappMessageSearchAdapter([$unsupportedHandler, $supportedHandler]))->search(
        intent: 'search_technical_notebook',
        filters: $filters,
    );

    expect($result)->toBe($payload);
});

it('returns an empty result for unsupported whatsapp search intents', function () {
    $handler = Mockery::mock(WhatsappMessageSearchHandlerInterface::class);
    $handler->shouldReceive('supports')
        ->once()
        ->with('unsupported_intent')
        ->andReturnFalse();
    $handler->shouldReceive('search')->never();

    $result = (new WhatsappMessageSearchAdapter([$handler]))->search(
        intent: 'unsupported_intent',
        filters: ['municipality' => 'Antas'],
    );

    expect($result)->toBe([
        'term' => null,
        'total' => 0,
        'data' => [],
    ]);
});
