<?php

use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Conversation\Application\Interfaces\Parser\WhatsappMessageInterpretationParserInterface;
use App\Core\Conversation\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\InterpretWhatsappMessageWithAiServiceInterface;
use App\Core\Conversation\Application\Service\ResolveWhatsappMessageInterpretationService;
use App\Core\Conversation\Domain\Resolver\WhatsappMessageIntentResolver;
use App\Core\Conversation\Enum\ConversationStateEnum;

it('uses direct interpretation before ai interpretation', function () {
    $directInterpreter = Mockery::mock(DirectWhatsappMessageInterpreterServiceInterface::class);
    $directInterpreter->shouldReceive('interpret')
        ->once()
        ->with('Quero consultar o processo 020.4487.2021.0009714-69')
        ->andReturn(new WhatsappMessageInterpretationDTO(
            intent: 'search_technical_notebook',
            filters: ['process' => '020.4487.2021.0009714-69'],
        ));

    $aiInterpreter = Mockery::mock(InterpretWhatsappMessageWithAiServiceInterface::class);
    $aiInterpreter->shouldReceive('__invoke')->never();

    $parser = Mockery::mock(WhatsappMessageInterpretationParserInterface::class);
    $parser->shouldReceive('parse')->never();

    $interpretation = (new ResolveWhatsappMessageInterpretationService(
        directInterpreter: $directInterpreter,
        aiInterpreter: $aiInterpreter,
        parser: $parser,
        resolver: new WhatsappMessageIntentResolver,
    ))('Quero consultar o processo 020.4487.2021.0009714-69');

    expect($interpretation->intent)->toBe('search_technical_notebook')
        ->and($interpretation->filters)->toBe([
            'process' => '020.4487.2021.0009714-69',
        ]);
});

it('parses ai interpretation when no direct interpretation is found', function () {
    $directInterpreter = Mockery::mock(DirectWhatsappMessageInterpreterServiceInterface::class);
    $directInterpreter->shouldReceive('interpret')
        ->once()
        ->with('Buscar levantamento de terreno em Antas')
        ->andReturnNull();

    $aiInterpreter = Mockery::mock(InterpretWhatsappMessageWithAiServiceInterface::class);
    $aiInterpreter->shouldReceive('__invoke')
        ->once()
        ->with('Buscar levantamento de terreno em Antas')
        ->andReturn('{"intent":"search_land_survey","filters":{"municipality":"Antas"}}');

    $parser = Mockery::mock(WhatsappMessageInterpretationParserInterface::class);
    $parser->shouldReceive('parse')
        ->once()
        ->with('{"intent":"search_land_survey","filters":{"municipality":"Antas"}}')
        ->andReturn(new WhatsappMessageInterpretationDTO(
            intent: 'search_land_survey',
            filters: ['municipality' => 'Antas'],
        ));

    $interpretation = (new ResolveWhatsappMessageInterpretationService(
        directInterpreter: $directInterpreter,
        aiInterpreter: $aiInterpreter,
        parser: $parser,
        resolver: new WhatsappMessageIntentResolver,
    ))('Buscar levantamento de terreno em Antas');

    expect($interpretation->intent)->toBe('search_technical_notebook')
        ->and($interpretation->filters)->toBe(['municipality' => 'Antas']);
});

it('interprets option one as build panel only when conversation is in the main menu', function () {
    $directInterpreter = Mockery::mock(DirectWhatsappMessageInterpreterServiceInterface::class);
    $directInterpreter->shouldReceive('interpret')->never();

    $aiInterpreter = Mockery::mock(InterpretWhatsappMessageWithAiServiceInterface::class);
    $aiInterpreter->shouldReceive('__invoke')->never();

    $parser = Mockery::mock(WhatsappMessageInterpretationParserInterface::class);
    $parser->shouldReceive('parse')->never();

    $interpretation = (new ResolveWhatsappMessageInterpretationService(
        directInterpreter: $directInterpreter,
        aiInterpreter: $aiInterpreter,
        parser: $parser,
        resolver: new WhatsappMessageIntentResolver,
    ))('1', ConversationStateEnum::MainMenu);

    expect($interpretation->intent)->toBe('open_build_panel')
        ->and($interpretation->filters)->toBe([]);
});
