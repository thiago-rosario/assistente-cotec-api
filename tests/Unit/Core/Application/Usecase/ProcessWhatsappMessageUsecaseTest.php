<?php

use App\BuildPanel\Application\DTO\WhatsappMessageInterpretationDTO;
use App\BuildPanel\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\BuildPanel\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\BuildPanel\Application\Service\BuildPanelWhatsappMessageService;
use App\BuildPanel\Application\Service\MunicipalityExtractorService;
use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Factory\MessageFactory;
use App\Core\Application\Handler\BuildPanelFallbackWhatsappConversationFlowHandler;
use App\Core\Application\Handler\BuildPanelStateWhatsappConversationFlowHandler;
use App\Core\Application\Handler\MainMenuOptionWhatsappConversationFlowHandler;
use App\Core\Application\Handler\MainMenuRequestWhatsappConversationFlowHandler;
use App\Core\Application\Handler\UnsupportedWhatsappMessageContentHandler;
use App\Core\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Application\Service\WhatsappBuildPanelFlowService;
use App\Core\Application\Service\WhatsappConversationFlowService;
use App\Core\Application\Service\WhatsappMainMenuService;
use App\Core\Application\Service\WhatsappMessageProcessorService;
use App\Core\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Domain\Resolver\MessageIntentResolver;
use App\Core\Infra\Message\WhatsappMainMenuMessageBuilder;
use App\Core\Infra\Repository\EloquentRepository\CacheWhatsappConversationStateRepository;
use App\TechnicalInspectionReport\Application\Interfaces\Service\TechnicalInspectionReportWhatsappConversationFlowServiceInterface;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Cache;
use OpenAI\Exceptions\RateLimitException;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config(['cache.default' => 'array']);

    Cache::flush();
});

it('keeps the whatsapp message use case as an execution-only orchestrator', function () {
    $reflection = new ReflectionClass(ProcessWhatsappMessageUsecase::class);
    $publicExecutionMethods = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
        ->reject(fn (ReflectionMethod $method): bool => $method->isConstructor())
        ->map(fn (ReflectionMethod $method): string => $method->getName())
        ->values()
        ->all();

    expect($reflection->getMethods(ReflectionMethod::IS_PRIVATE))->toBeEmpty()
        ->and($publicExecutionMethods)->toBe(['__invoke']);
});

it('answers greeting messages with the main menu without resolving interpretation or searching records', function () {
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

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
    ))(new ReceivedMessageInputDTO(
        message: 'Olá!',
        phone: '5571999999999',
    ));

    expect($result['intent'])->toBe('main_menu')
        ->and($result['reply'])->toContain('1️⃣  Consultar o Painel de Obras')
        ->and($result['reply'])->toContain('2️⃣  Consultar ou cadastrar Relatórios de Vistoria Técnica')
        ->and($result['reply'])->toContain('3️⃣  Ajuda')
        ->and($result['reply'])->toContain('Você também pode enviar diretamente o nome de um município')
        ->and(Cache::get('whatsapp:conversation:5571999999999'))->toBe('main_menu');
});

it('clears the conversation after 0 and starts a new interaction on the next greeting', function () {
    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')
        ->once()
        ->with('Oi')
        ->andReturnTrue();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')->never();

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);

    $usecase = processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
    );

    $ended = $usecase(new ReceivedMessageInputDTO(
        message: '0',
        phone: '5571999999999',
    ));

    expect($ended['intent'])->toBe('conversation_ended')
        ->and(Cache::get('whatsapp:conversation:5571999999999'))->toBeNull();

    $restarted = $usecase(new ReceivedMessageInputDTO(
        message: 'Oi',
        phone: '5571999999999',
    ));

    expect($restarted['intent'])->toBe('main_menu')
        ->and(Cache::get('whatsapp:conversation:5571999999999'))->toBe('main_menu');
});

it('starts the build panel flow from the main menu option', function () {
    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')->never();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')->never();

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('greeting')
        ->once()
        ->andReturn([
            'reply' => 'Envie o nome do município ou o número do processo.',
            'intent' => 'greeting',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
    ))(new ReceivedMessageInputDTO(
        message: '1',
        phone: '5571999999999',
    ));

    expect($result['intent'])->toBe('greeting')
        ->and(Cache::get('whatsapp:conversation:5571999999999'))->toBe('build_panel');
});

it('ends the build panel conversation after returning search results', function () {
    Cache::put('whatsapp:conversation:5571999999999', 'build_panel');

    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')
        ->once()
        ->with('Olá!')
        ->andReturnTrue();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')
        ->once()
        ->with('ANDARAÍ')
        ->andReturn(new WhatsappMessageInterpretationDTO(
            intent: 'search_technical_notebook',
            filters: ['municipality' => 'ANDARAÍ'],
        ));

    $searchResult = [
        'term' => null,
        'total' => 1,
        'data' => [
            [
                'municipality' => 'ANDARAÍ',
            ],
        ],
    ];

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')
        ->once()
        ->with('search_technical_notebook', ['municipality' => 'ANDARAÍ'])
        ->andReturn($searchResult);

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('format')
        ->once()
        ->with('search_technical_notebook', ['municipality' => 'ANDARAÍ'], $searchResult)
        ->andReturn([
            'reply' => 'Encontrei 1 registro para o município ANDARAÍ.',
            'intent' => 'search_technical_notebook',
            'total' => 1,
            'data' => $searchResult['data'],
            'filters' => ['municipality' => 'ANDARAÍ'],
        ]);

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(
            accepts: true,
            intent: 'search_technical_notebook',
            filters: ['municipality' => 'ANDARAÍ'],
        ),
    ))(new ReceivedMessageInputDTO(
        message: 'ANDARAÍ',
        phone: '5571999999999',
    ));

    expect($result['intent'])->toBe('search_technical_notebook')
        ->and(Cache::get('whatsapp:conversation:5571999999999'))->toBeNull();

    $nextResult = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
    ))(new ReceivedMessageInputDTO(
        message: 'Olá!',
        phone: '5571999999999',
    ));

    expect($nextResult['intent'])->toBe('main_menu');
});

it('keeps the consultation-specific unknown response inside the build panel flow', function () {
    Cache::put('whatsapp:conversation:5571999999999', 'build_panel');

    $greetingMatcher = Mockery::mock(GreetingMessageMatcherServiceInterface::class);
    $greetingMatcher->shouldReceive('matches')->never();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')
        ->once()
        ->with('mensagem sem consulta')
        ->andReturn(new WhatsappMessageInterpretationDTO(intent: 'unknown'));

    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();

    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('unknownIntent')
        ->once()
        ->andReturn([
            'reply' => 'Não consegui identificar o município ou processo informado.',
            'intent' => 'unknown',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);
    $responseFormatter->shouldReceive('globalUnknownIntent')->never();

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
    ))(new ReceivedMessageInputDTO(
        message: 'mensagem sem consulta',
        phone: '5571999999999',
    ));

    expect($result['intent'])->toBe('unknown')
        ->and($result['reply'])->toContain('município ou processo');
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

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(
            accepts: true,
            intent: 'search_technical_notebook',
            filters: ['municipality' => 'Antas'],
        ),
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
    $responseFormatter->shouldReceive('globalUnknownIntent')
        ->once()
        ->andReturn([
            'reply' => '🤔 Não entendi sua mensagem.',
            'intent' => 'unknown',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
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
    $responseFormatter->shouldReceive('globalUnknownIntent')
        ->once()
        ->andReturn([
            'reply' => '🤔 Não entendi sua mensagem.',
            'intent' => 'unknown',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(
            accepts: false,
            intent: 'search_technical_notebook',
            filters: ['force' => 'PC'],
        ),
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

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
    ))(new ReceivedMessageInputDTO(message: ''));

    expect($result['intent'])->toBe('unsupported_message_content');
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

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
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

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(
            accepts: true,
            intent: 'search_technical_notebook',
            filters: ['process' => '030.2647.2023.0170476-39'],
        ),
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
        ->with('Quero consultar ANDARAÍ')
        ->andReturnFalse();

    $resolveInterpretation = Mockery::mock(ResolveWhatsappMessageInterpretationServiceInterface::class);
    $resolveInterpretation->shouldReceive('__invoke')
        ->once()
        ->with('Quero consultar ANDARAÍ')
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

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(
            accepts: true,
            intent: 'search_technical_notebook',
            filters: ['municipality' => 'ANDARAÍ'],
        ),
    ))(new ReceivedMessageInputDTO(message: 'Quero consultar ANDARAÍ'));

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

    $result = (processWhatsappMessageUsecase(
        greetingMatcher: $greetingMatcher,
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        service: acceptedWhatsappMessageInterpretationServiceMock(),
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

function processWhatsappMessageUsecase(
    GreetingMessageMatcherServiceInterface $greetingMatcher,
    ResolveWhatsappMessageInterpretationServiceInterface $resolveInterpretation,
    WhatsappMessageSearchAdapterInterface $searchAdapter,
    WhatsappMessageResponseFormatterInterface $responseFormatter,
    AcceptedWhatsappMessageInterpretationServiceInterface $service,
): ProcessWhatsappMessageUsecase {
    $conversationStates = new CacheWhatsappConversationStateRepository(Cache::store());
    $intentResolver = new MessageIntentResolver;
    $buildPanelMessages = new BuildPanelWhatsappMessageService(
        resolveInterpretation: $resolveInterpretation,
        searchAdapter: $searchAdapter,
        responseFormatter: $responseFormatter,
        acceptedInterpretation: $service,
        conversationStates: $conversationStates,
    );
    $mainMenu = new WhatsappMainMenuService(
        conversationStates: $conversationStates,
        responseFormatter: $responseFormatter,
        buildPanelMessages: $buildPanelMessages,
        messages: new WhatsappMainMenuMessageBuilder,
        technicalInspectionReportFlow: Mockery::mock(TechnicalInspectionReportWhatsappConversationFlowServiceInterface::class),
    );
    $buildPanelFlow = new WhatsappBuildPanelFlowService(
        mainMenu: $mainMenu,
        buildPanelMessages: $buildPanelMessages,
        intentResolver: $intentResolver,
    );

    return new ProcessWhatsappMessageUsecase(
        messages: new MessageFactory,
        processor: new WhatsappMessageProcessorService(
            flow: new WhatsappConversationFlowService(
                handlers: [
                    new UnsupportedWhatsappMessageContentHandler($responseFormatter),
                    new BuildPanelStateWhatsappConversationFlowHandler($conversationStates, $buildPanelFlow),
                    new MainMenuOptionWhatsappConversationFlowHandler($intentResolver, $mainMenu),
                    new MainMenuRequestWhatsappConversationFlowHandler(
                        $intentResolver,
                        $greetingMatcher,
                        new MunicipalityExtractorService,
                        $mainMenu,
                    ),
                    new BuildPanelFallbackWhatsappConversationFlowHandler($buildPanelMessages),
                ],
            ),
            responseFormatter: $responseFormatter,
        ),
    );
}
