<?php

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\InterpretWhatsappMessageWithAiServiceInterface;
use App\Core\Application\Interfaces\WhatsappMessageInterpretationParserInterface;
use App\Core\Application\Interfaces\WhatsappMessageSearchAdapterInterface;
use App\Core\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Infra\Service\WhatsappMessageResponseFormatter;

it('answers simple greetings without using ai interpretation or searching records', function (string $message) {
    $aiInterpreter = Mockery::mock(InterpretWhatsappMessageWithAiServiceInterface::class);
    $aiInterpreter->shouldReceive('__invoke')->never();

    $interpretationParser = Mockery::mock(WhatsappMessageInterpretationParserInterface::class);
    $interpretationParser->shouldReceive('parse')->never();

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $result = (new ProcessWhatsappMessageUsecase(
        aiInterpreter: $aiInterpreter,
        interpretationParser: $interpretationParser,
        searchAdapter: $searchAdapter,
        responseFormatter: new WhatsappMessageResponseFormatter,
    ))(new ReceivedMessageInputDTO(message: $message));

    expect($result)->toBe([
        'reply' => 'Oi! Posso ajudar com consultas por número do processo, município, força, região ou situação.',
        'intent' => 'greeting',
        'total' => 0,
        'data' => [],
        'filters' => [],
    ]);
})->with([
    'oi' => 'oi',
    'oi prolongado' => 'Oiii',
    'olá com pontuação' => 'Olá!',
    'bom dia' => 'bom dia',
]);

it('searches technical notebook by process without using ai interpretation', function () {
    $aiInterpreter = Mockery::mock(InterpretWhatsappMessageWithAiServiceInterface::class);
    $aiInterpreter->shouldReceive('__invoke')->never();

    $interpretationParser = Mockery::mock(WhatsappMessageInterpretationParserInterface::class);
    $interpretationParser->shouldReceive('parse')->never();

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')
        ->once()
        ->with('search_technical_notebook', ['process' => '020.4487.2021.0009714-69'])
        ->andReturn([
            'term' => null,
            'total' => 1,
            'data' => [
                [
                    'process' => '020.4487.2021.0009714-69',
                    'municipality' => 'ANTAS',
                    'force' => 'PC',
                    'claim' => 'CONSTRUÇÃO/REFORMA',
                    'typology' => '1B',
                    'estimatedValue' => 1335654.53,
                    'landStatus' => 'APROVADO',
                    'contract' => '20/2021',
                ],
            ],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        aiInterpreter: $aiInterpreter,
        interpretationParser: $interpretationParser,
        searchAdapter: $searchAdapter,
        responseFormatter: new WhatsappMessageResponseFormatter,
    ))(new ReceivedMessageInputDTO(message: 'Quero consultar o processo 020.4487.2021.0009714-69'));

    expect($result['reply'])->toBe(implode(PHP_EOL, [
        '📋 Consulta encontrada no Caderno Técnico',
        '',
        '📄 Processo: 020.4487.2021.0009714-69',
        '🏙️ Município: ANTAS',
        '👮 Força: PC',
        '🏗️ Pleito: CONSTRUÇÃO/REFORMA',
        '🏢 Tipologia: 1B',
        '💰 Valor estimado: R$ 1.335.654,53',
        '📌 Situação do terreno: APROVADO',
        '📑 Contrato: 20/2021',
    ]))
        ->and($result['intent'])->toBe('search_technical_notebook')
        ->and($result['total'])->toBe(1)
        ->and($result['filters'])->toBe(['process' => '020.4487.2021.0009714-69']);
});

it('searches technical notebooks by municipality without using ai interpretation', function (string $message) {
    $aiInterpreter = Mockery::mock(InterpretWhatsappMessageWithAiServiceInterface::class);
    $aiInterpreter->shouldReceive('__invoke')->never();

    $interpretationParser = Mockery::mock(WhatsappMessageInterpretationParserInterface::class);
    $interpretationParser->shouldReceive('parse')->never();

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')
        ->once()
        ->with('search_technical_notebook', ['municipality' => 'Antas'])
        ->andReturn([
            'term' => null,
            'total' => 2,
            'data' => [
                [
                    'process' => '020.4487.2021.0009714-69',
                    'municipality' => 'ANTAS',
                    'claim' => 'CONSTRUÇÃO/REFORMA',
                    'buildStatus' => 'LICITADO',
                ],
                [
                    'process' => '020.4487.2021.0009715-40',
                    'municipality' => 'ANTAS',
                    'claim' => 'AMPLIAÇÃO',
                    'landStatus' => 'APROVADO',
                ],
            ],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        aiInterpreter: $aiInterpreter,
        interpretationParser: $interpretationParser,
        searchAdapter: $searchAdapter,
        responseFormatter: new WhatsappMessageResponseFormatter,
    ))(new ReceivedMessageInputDTO(message: $message));

    expect($result['reply'])->toBe(implode(PHP_EOL, [
        'Encontrei 2 registros para o município ANTAS.',
        '',
        '1. Processo: 020.4487.2021.0009714-69',
        '   Pleito: CONSTRUÇÃO/REFORMA',
        '   Situação: LICITADO',
        '',
        '2. Processo: 020.4487.2021.0009715-40',
        '   Pleito: AMPLIAÇÃO',
        '   Situação: APROVADO',
    ]))
        ->and($result['intent'])->toBe('search_technical_notebook')
        ->and($result['total'])->toBe(2)
        ->and($result['filters'])->toBe(['municipality' => 'Antas']);
})->with([
    'municipio direto' => 'Município Antas',
    'pergunta por obras' => 'Quais obras existem no município de Antas?',
]);

it('keeps municipality replies as a counted list when only one record is found', function () {
    $aiInterpreter = Mockery::mock(InterpretWhatsappMessageWithAiServiceInterface::class);
    $aiInterpreter->shouldReceive('__invoke')->never();

    $interpretationParser = Mockery::mock(WhatsappMessageInterpretationParserInterface::class);
    $interpretationParser->shouldReceive('parse')->never();

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')
        ->once()
        ->with('search_technical_notebook', ['municipality' => 'Antas'])
        ->andReturn([
            'term' => null,
            'total' => 1,
            'data' => [
                [
                    'process' => '020.4487.2021.0009714-69',
                    'municipality' => 'ANTAS',
                    'claim' => 'CONSTRUÇÃO/REFORMA',
                    'buildStatus' => 'LICITADO',
                ],
            ],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        aiInterpreter: $aiInterpreter,
        interpretationParser: $interpretationParser,
        searchAdapter: $searchAdapter,
        responseFormatter: new WhatsappMessageResponseFormatter,
    ))(new ReceivedMessageInputDTO(message: 'Município Antas'));

    expect($result['reply'])->toBe(implode(PHP_EOL, [
        'Encontrei 1 registro para o município ANTAS.',
        '',
        '1. Processo: 020.4487.2021.0009714-69',
        '   Pleito: CONSTRUÇÃO/REFORMA',
        '   Situação: LICITADO',
    ]));
});

it('defaults ai interpreted searches to technical notebook', function () {
    $aiInterpreter = Mockery::mock(InterpretWhatsappMessageWithAiServiceInterface::class);
    $aiInterpreter->shouldReceive('__invoke')
        ->once()
        ->with('Buscar levantamento de terreno em Antas')
        ->andReturn('{"intent":"search_land_survey","filters":{"municipality":"Antas"}}');

    $interpretationParser = Mockery::mock(WhatsappMessageInterpretationParserInterface::class);
    $interpretationParser->shouldReceive('parse')
        ->once()
        ->with('{"intent":"search_land_survey","filters":{"municipality":"Antas"}}')
        ->andReturn([
            'intent' => 'search_land_survey',
            'filters' => [
                'municipality' => 'Antas',
            ],
        ]);

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')
        ->once()
        ->with('search_technical_notebook', ['municipality' => 'Antas'])
        ->andReturn([
            'term' => null,
            'total' => 1,
            'data' => [
                [
                    'process' => '020.4487.2021.0009714-69',
                    'municipality' => 'ANTAS',
                    'claim' => 'CONSTRUÇÃO/REFORMA',
                    'buildStatus' => 'LICITADO',
                ],
            ],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        aiInterpreter: $aiInterpreter,
        interpretationParser: $interpretationParser,
        searchAdapter: $searchAdapter,
        responseFormatter: new WhatsappMessageResponseFormatter,
    ))(new ReceivedMessageInputDTO(message: 'Buscar levantamento de terreno em Antas'));

    expect($result['intent'])->toBe('search_technical_notebook')
        ->and($result['filters'])->toBe(['municipality' => 'Antas']);
});

it('keeps unknown messages unknown when no filters are identified', function () {
    $aiInterpreter = Mockery::mock(InterpretWhatsappMessageWithAiServiceInterface::class);
    $aiInterpreter->shouldReceive('__invoke')
        ->once()
        ->with('Qual é a previsão do tempo?')
        ->andReturn('{"intent":"unknown","filters":{}}');

    $interpretationParser = Mockery::mock(WhatsappMessageInterpretationParserInterface::class);
    $interpretationParser->shouldReceive('parse')
        ->once()
        ->with('{"intent":"unknown","filters":{}}')
        ->andReturn([
            'intent' => 'unknown',
            'filters' => [],
        ]);

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $result = (new ProcessWhatsappMessageUsecase(
        aiInterpreter: $aiInterpreter,
        interpretationParser: $interpretationParser,
        searchAdapter: $searchAdapter,
        responseFormatter: new WhatsappMessageResponseFormatter,
    ))(new ReceivedMessageInputDTO(message: 'Qual é a previsão do tempo?'));

    expect($result['intent'])->toBe('unknown')
        ->and($result['filters'])->toBe([]);
});
