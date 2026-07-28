<?php

use App\Core\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Application\Service\ResolveWhatsappMessageInterpretationService;
use App\Core\Domain\Resolver\WhatsappMessageIntentResolver;

it('uses direct interpretation without ai interpretation', function () {
    $directInterpreter = Mockery::mock(DirectWhatsappMessageInterpreterServiceInterface::class);
    $directInterpreter->shouldReceive('interpret')
        ->once()
        ->with('Quero consultar o processo 020.4487.2021.0009714-69')
        ->andReturn(new WhatsappMessageInterpretationDTO(
            intent: 'search_technical_notebook',
            filters: ['process' => '020.4487.2021.0009714-69'],
        ));

    $interpretation = (new ResolveWhatsappMessageInterpretationService(
        directInterpreter: $directInterpreter,
        resolver: new WhatsappMessageIntentResolver,
    ))('Quero consultar o processo 020.4487.2021.0009714-69');

    expect($interpretation->intent)->toBe('search_technical_notebook')
        ->and($interpretation->filters)->toBe([
            'process' => '020.4487.2021.0009714-69',
        ]);
});

it('returns unknown when no direct interpretation is found', function () {
    $directInterpreter = Mockery::mock(DirectWhatsappMessageInterpreterServiceInterface::class);
    $directInterpreter->shouldReceive('interpret')
        ->once()
        ->with('Buscar levantamento de terreno em Antas')
        ->andReturnNull();

    $interpretation = (new ResolveWhatsappMessageInterpretationService(
        directInterpreter: $directInterpreter,
        resolver: new WhatsappMessageIntentResolver,
    ))('Buscar levantamento de terreno em Antas');

    expect($interpretation->intent)->toBe('unknown')
        ->and($interpretation->filters)->toBe([]);
});
