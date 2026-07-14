<?php

use App\Core\Conversation\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Conversation\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\Core\Conversation\Application\Interfaces\Repository\ConversationStateRepositoryInterface;
use App\Core\Conversation\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Conversation\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Conversation\Enum\ConversationState;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Contracts\Debug\ExceptionHandler;
use OpenAI\Exceptions\RateLimitException;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

uses(TestCase::class);

it('answers greeting messages without resolving interpretation or searching records', function () {
    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')
        ->once()
        ->with('Olá!')
        ->andReturnTrue();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')->never();

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('greeting')
        ->once()
        ->andReturn([
            'reply' => 'Oi! Posso ajudar com consultas por número do processo, município, força, região ou situação.',
            'intent' => 'greeting',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $input = new ReceivedMessageInputDTO(message: 'Olá!', phone: '5571999999999');
    $conversationStateRepository = conversationStateRepositoryMock();
    $conversationStateRepository->shouldReceive('put')
        ->once()
        ->with($input, ConversationState::MainMenu);

    $result = (new ProcessWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
        conversationStateRepository: $conversationStateRepository,
    ))($input);

    expect($result['intent'])->toBe('greeting');
});

it('searches and formats resolved whatsapp message interpretations', function () {
    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')
        ->once()
        ->with('Quero consultar Antas')
        ->andReturnFalse();

    $interpretation = new WhatsappMessageInterpretationDTO(
        intent: 'search_technical_notebook',
        filters: ['municipality' => 'Antas'],
    );

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')
        ->once()
        ->with('Quero consultar Antas')
        ->andReturn($interpretation);

    $searchResult = [
        'term' => null,
        'total' => 1,
        'data' => [
            [
                'process' => '020.4487.2021.0009714-69',
                'municipality' => 'ANTAS',
            ],
        ],
    ];

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')
        ->once()
        ->with('search_technical_notebook', ['municipality' => 'Antas'])
        ->andReturn($searchResult);

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('format')
        ->once()
        ->with('search_technical_notebook', ['municipality' => 'Antas'], $searchResult)
        ->andReturn([
            'reply' => 'Encontrei 1 registro para o município ANTAS.',
            'intent' => 'search_technical_notebook',
            'total' => 1,
            'data' => $searchResult['data'],
            'filters' => ['municipality' => 'Antas'],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(
            accepts: true,
            intent: 'search_technical_notebook',
            filters: ['municipality' => 'Antas'],
        ),
        conversationStateRepository: conversationStateRepositoryMock(),
    ))(new ReceivedMessageInputDTO(message: 'Quero consultar Antas'));

    expect($result)
        ->toHaveKey('reply', 'Encontrei 1 registro para o município ANTAS.')
        ->and($result['filters'])->toBe(['municipality' => 'Antas']);
});

it('returns unknown response when interpretation stays unknown', function () {
    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')
        ->once()
        ->with('Qual é a previsão do tempo?')
        ->andReturnFalse();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')
        ->once()
        ->with('Qual é a previsão do tempo?')
        ->andReturn(new WhatsappMessageInterpretationDTO(intent: 'unknown'));

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('unknownIntent')
        ->once()
        ->andReturn([
            'reply' => 'Não consegui identificar exatamente qual consulta você deseja fazer.',
            'intent' => 'unknown',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
        conversationStateRepository: conversationStateRepositoryMock(),
    ))(new ReceivedMessageInputDTO(message: 'Qual é a previsão do tempo?'));

    expect($result['intent'])->toBe('unknown');
});

it('does not search technical notebooks without municipality or sei process filters', function () {
    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')
        ->once()
        ->with('Quero consultar por força PC')
        ->andReturnFalse();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')
        ->once()
        ->with('Quero consultar por força PC')
        ->andReturn(new WhatsappMessageInterpretationDTO(
            intent: 'search_technical_notebook',
            filters: ['force' => 'PC'],
        ));

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('unknownIntent')
        ->once()
        ->andReturn([
            'reply' => 'Não consegui identificar exatamente qual consulta você deseja fazer.',
            'intent' => 'unknown',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(
            accepts: false,
            intent: 'search_technical_notebook',
            filters: ['force' => 'PC'],
        ),
        conversationStateRepository: conversationStateRepositoryMock(),
    ))(new ReceivedMessageInputDTO(message: 'Quero consultar por força PC'));

    expect($result['intent'])->toBe('unknown');
});

it('handles messages without text content in php without resolving interpretation', function () {
    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')->never();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')->never();

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('unsupportedMessageContent')
        ->once()
        ->andReturn([
            'reply' => 'Recebi sua mensagem, mas não consegui ler conteúdo em texto.',
            'intent' => 'unsupported_message_content',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
        conversationStateRepository: conversationStateRepositoryMock(),
    ))(new ReceivedMessageInputDTO(message: ''));

    expect($result['intent'])->toBe('unsupported_message_content');
});

it('redirects the main menu build panel option to the build panel module', function () {
    $input = new ReceivedMessageInputDTO(message: '1', phone: '5571999999999');

    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')
        ->once()
        ->with('1')
        ->andReturnFalse();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')
        ->once()
        ->with('1', ConversationState::MainMenu)
        ->andReturn(new WhatsappMessageInterpretationDTO(intent: 'open_build_panel'));

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('buildPanelConsultation')
        ->once()
        ->andReturn([
            'reply' => <<<'TEXT'
Olá! Você acessou o módulo *Painel de Obras*.

Aqui você pode consultar informações sobre obras por meio de uma destas opções:

• Nome do município
• Número do processo SEI

Envie apenas uma informação por vez para iniciar a consulta.
TEXT,
            'intent' => 'search_technical_notebook',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $conversationStateRepository = conversationStateRepositoryMock();
    $conversationStateRepository->shouldReceive('get')
        ->once()
        ->with($input)
        ->andReturn(ConversationState::MainMenu);
    $conversationStateRepository->shouldReceive('put')
        ->once()
        ->with($input, ConversationState::BuildPanelConsultation);

    $result = (new ProcessWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
        conversationStateRepository: $conversationStateRepository,
    ))($input);

    expect($result['reply'])->toBe(<<<'TEXT'
Olá! Você acessou o módulo *Painel de Obras*.

Aqui você pode consultar informações sobre obras por meio de uma destas opções:

• Nome do município
• Número do processo SEI

Envie apenas uma informação por vez para iniciar a consulta.
TEXT)
        ->and($result['intent'])->toBe('search_technical_notebook');
});

it('returns rate limited response without reporting when openai limit is exceeded', function () {
    $exceptionHandler = Mockery::mock(ExceptionHandler::class);
    $exceptionHandler->shouldReceive('report')->never();
    app()->instance(ExceptionHandler::class, $exceptionHandler);

    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')
        ->once()
        ->with('Quero consultar Antas')
        ->andReturnFalse();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')
        ->once()
        ->with('Quero consultar Antas')
        ->andThrow(new RateLimitException(Mockery::mock(ResponseInterface::class)));

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('rateLimited')
        ->once()
        ->andReturn([
            'reply' => 'Recebi sua mensagem, mas o serviço de interpretação está temporariamente no limite.',
            'intent' => 'rate_limited',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
        conversationStateRepository: conversationStateRepositoryMock(),
    ))(new ReceivedMessageInputDTO(message: 'Quero consultar Antas'));

    expect($result['intent'])->toBe('rate_limited');
});

it('returns data source unavailable response when external search cannot connect', function () {
    $exceptionHandler = Mockery::mock(ExceptionHandler::class);
    $exceptionHandler->shouldReceive('report')->never();
    app()->instance(ExceptionHandler::class, $exceptionHandler);

    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')
        ->once()
        ->with('030.2647.2023.0170476-39')
        ->andReturnFalse();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')
        ->once()
        ->with('030.2647.2023.0170476-39')
        ->andReturn(new WhatsappMessageInterpretationDTO(
            intent: 'search_technical_notebook',
            filters: ['process' => '030.2647.2023.0170476-39'],
        ));

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')
        ->once()
        ->with('search_technical_notebook', ['process' => '030.2647.2023.0170476-39'])
        ->andThrow(new ConnectException('Could not resolve host.', new Request('GET', 'https://sheets.googleapis.com')));

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('dataSourceUnavailable')
        ->once()
        ->andReturn([
            'reply' => 'Entendi sua consulta, mas não consegui acessar a fonte de dados agora.',
            'intent' => 'data_source_unavailable',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(
            accepts: true,
            intent: 'search_technical_notebook',
            filters: ['process' => '030.2647.2023.0170476-39'],
        ),
        conversationStateRepository: conversationStateRepositoryMock(),
    ))(new ReceivedMessageInputDTO(message: '030.2647.2023.0170476-39'));

    expect($result['intent'])->toBe('data_source_unavailable');
});

it('returns data source unavailable response and reports when google sheets rejects credentials', function () {
    $exceptionHandler = Mockery::mock(ExceptionHandler::class);
    $exceptionHandler->shouldReceive('report')
        ->once()
        ->with(Mockery::type(GoogleServiceException::class));
    app()->instance(ExceptionHandler::class, $exceptionHandler);

    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')
        ->once()
        ->with('ANDARAÍ')
        ->andReturnFalse();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')
        ->once()
        ->with('ANDARAÍ')
        ->andReturn(new WhatsappMessageInterpretationDTO(
            intent: 'search_technical_notebook',
            filters: ['municipality' => 'ANDARAÍ'],
        ));

    $googleException = new GoogleServiceException(
        '{"error":"invalid_grant","error_description":"Invalid JWT Signature."}',
        400,
    );

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')
        ->once()
        ->with('search_technical_notebook', ['municipality' => 'ANDARAÍ'])
        ->andThrow($googleException);

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('dataSourceUnavailable')
        ->once()
        ->andReturn([
            'reply' => 'Entendi sua consulta, mas não consegui acessar a fonte de dados agora.',
            'intent' => 'data_source_unavailable',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(
            accepts: true,
            intent: 'search_technical_notebook',
            filters: ['municipality' => 'ANDARAÍ'],
        ),
        conversationStateRepository: conversationStateRepositoryMock(),
    ))(new ReceivedMessageInputDTO(message: 'ANDARAÍ'));

    expect($result['intent'])->toBe('data_source_unavailable');
});

it('returns error response when processing fails', function () {
    $exceptionHandler = Mockery::mock(ExceptionHandler::class);
    $exceptionHandler->shouldReceive('report')
        ->once()
        ->with(Mockery::type(RuntimeException::class));
    app()->instance(ExceptionHandler::class, $exceptionHandler);

    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')
        ->once()
        ->andThrow(RuntimeException::class);

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')->never();

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('error')
        ->once()
        ->andReturn([
            'reply' => 'Não consegui processar sua solicitação agora.',
            'intent' => 'error',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
        conversationStateRepository: conversationStateRepositoryMock(),
    ))(new ReceivedMessageInputDTO(message: 'Olá!'));

    expect($result['intent'])->toBe('error');
});

/**
 * @param  array<string, mixed>|null  $filters
 */
function acceptedWhatsappMessageInterpretationServiceMock(
    ?bool $accepts = null,
    ?string $intent = null,
    ?array $filters = null,
): AcceptedWhatsappMessageInterpretationServiceInterface {
    $service = Mockery::mock(AcceptedWhatsappMessageInterpretationServiceInterface::class);

    if ($accepts === null) {
        $service->shouldReceive('accepts')->never();

        return $service;
    }

    $expectation = $service->shouldReceive('accepts')->once();

    if ($intent !== null && $filters !== null) {
        $expectation->with($intent, $filters);
    }

    $expectation->andReturn($accepts);

    return $service;
}

function conversationStateRepositoryMock(): ConversationStateRepositoryInterface
{
    return Mockery::mock(ConversationStateRepositoryInterface::class)->shouldIgnoreMissing();
}
