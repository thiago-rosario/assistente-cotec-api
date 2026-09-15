<?php

use App\BuildPanel\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\BuildPanel\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\BuildPanel\Application\Rules\SeiProcessWhatsappMessageInterpretationRule;
use App\Contract\Application\Interfaces\Service\ContractWhatsappMessageServiceInterface;
use App\Contract\Infra\Message\WhatsappContractDefaultReplies;
use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\DTO\WhatsappConversationStateDTO;
use App\Core\Application\Interfaces\Service\BuildPanelWhatsappMessageServiceInterface;
use App\Core\Application\Interfaces\Service\CoreWhatsappResponseFormatterInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Service\GreetingMessageMatcherService;
use App\Core\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Enum\WhatsappTerminalIntentEnum;
use App\Core\Infra\Message\WhatsappCoreDefaultReplies;
use App\Core\Infra\Message\WhatsappCoreResponsePayloadFactory;
use App\Core\Infra\Repository\WhatsappConversationStateStore;
use App\Core\Infra\Service\WhatsappCoreResponseFormatter;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Cache::flush();
});

it('opens the main menu for greetings and clears the neutral menu state', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();
    $coreResponseFormatter->shouldReceive('mainMenu')->once()->andReturn(whatsappCoreTestPayload('main_menu'));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $stateStore->put('5571999999999', new WhatsappConversationStateDTO(route: 'main_menu'));

    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        conversationState: $stateStore,
    );

    $result = $process(new ReceivedMessageInputDTO(
        message: 'Olá!',
        phone: '5571999999999',
    ));

    expect($result['intent'])->toBe('main_menu')
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

it('renders the requested core menu and municipality messages', function () {
    $formatter = new WhatsappCoreResponseFormatter(
        new WhatsappCoreDefaultReplies,
        new WhatsappCoreResponsePayloadFactory,
    );

    expect($formatter->mainMenu()['reply'])->toBe(
        "Olá! Eu sou o Assistente da COTEC. 👋\n\n"
        ."Posso ajudar você a consultar informações do *Painel de Obras da CEIRF/SSP* e acompanhar contratos.\n\n"
        ."Escolha uma das opções abaixo:\n\n"
        ."1️⃣ *Consultar o Painel de Obras*\n"
        ."Consulte informações por município ou número do processo.\n\n"
        ."2️⃣ *Acompanhar contratos*\n"
        ."Consulte aditivos, reajustes, prazos de execução e o resumo dos contratos.\n\n"
        .'Digite apenas o número da opção desejada.',
    )
        ->and($formatter->municipalityDisambiguation('Ibotirama')['reply'])
        ->toContain('*IBOTIRAMA*')
        ->toContain('1️⃣ Extrato de obras do município')
        ->toContain('2️⃣ Extrato consolidado dos contratos do município');

    expect($formatter->conversationClosed()['reply'])
        ->toContain('Consulta encerrada.')
        ->toContain('Agradecemos por utilizar o Assistente da COTEC!')
        ->and($formatter->queryCompleted()['reply'])->toBe('✅ Consulta concluída.');
});

it('identifies terminal responses by their explicit intent', function () {
    expect(WhatsappTerminalIntentEnum::fromResponse(whatsappCoreTestPayload('contract_summary')))
        ->toBe(WhatsappTerminalIntentEnum::ContractSummary)
        ->and(WhatsappTerminalIntentEnum::fromResponse(whatsappCoreTestPayload('contract_search_prompt')))
        ->toBeNull();
});

it('resolves the integrated whatsapp usecase from the application container', function () {
    expect(app(ProcessWhatsappMessageUsecaseInterface::class))
        ->toBeInstanceOf(ProcessWhatsappMessageUsecase::class);
});

it('keeps municipality disambiguation state until the contract summary is completed', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();
    $coreResponseFormatter->shouldReceive('municipalityDisambiguation')
        ->once()
        ->with('Ibotirama')
        ->andReturn(whatsappCoreTestPayload('municipality_disambiguation'));
    $contract = Mockery::mock(ContractWhatsappMessageServiceInterface::class);
    $contract->shouldReceive('search')
        ->once()
        ->with(4, 'Ibotirama')
        ->andReturn(whatsappCoreTestPayload('contract_summary', 1));
    $contract->shouldReceive('menu')->never();
    $contract->shouldReceive('searchPrompt')->never();

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        conversationState: $stateStore,
        contract: $contract,
    );

    $firstResult = $process(new ReceivedMessageInputDTO(
        message: 'Ibotirama',
        phone: '5571999999999',
    ));
    $state = $stateStore->get('5571999999999');

    $secondResult = $process(new ReceivedMessageInputDTO(
        message: '2',
        phone: '5571999999999',
    ));

    expect($firstResult['intent'])->toBe('municipality_disambiguation')
        ->and($state?->municipality)->toBe('Ibotirama')
        ->and($secondResult['intent'])->toBe('contract_summary')
        ->and($secondResult['reply'])->toBe("Resposta de teste\n\n✅ Consulta concluída.")
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

it('routes the selected municipality extract to the build panel using the stored municipality', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();
    $coreResponseFormatter->shouldReceive('municipalityDisambiguation')
        ->once()
        ->with('Feira de Santana')
        ->andReturn(whatsappCoreTestPayload('municipality_disambiguation'));
    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')
        ->once()
        ->with('Feira de Santana')
        ->andReturn(whatsappCoreTestPayload('search_technical_notebook', 1));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        buildPanel: $buildPanel,
        conversationState: $stateStore,
    );

    $firstResult = $process(new ReceivedMessageInputDTO(
        message: 'Feira de Santana',
        phone: '5571999999999',
    ));

    $result = $process(new ReceivedMessageInputDTO(
        message: '1',
        phone: '5571999999999',
    ));

    expect($firstResult['intent'])->toBe('municipality_disambiguation')
        ->and($result['intent'])->toBe('search_technical_notebook')
        ->and($result['total'])->toBe(1)
        ->and($result['reply'])->toBe("Resposta de teste\n\n✅ Consulta concluída.")
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

it('returns the main menu for a standalone sei process until the panel is selected', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();
    $coreResponseFormatter->shouldReceive('mainMenu')->once()->andReturn(whatsappCoreTestPayload('main_menu'));

    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')->never();

    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        buildPanel: $buildPanel,
    );

    $result = $process(new ReceivedMessageInputDTO(
        message: '020.4487.2021.0009714-69',
        phone: '5571999999999',
    ));

    expect($result['intent'])->toBe('main_menu');
});

it('keeps sei process lookup inside the selected build panel route', function () {
    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('greeting')->once()->andReturn(whatsappCoreTestPayload('greeting'));

    $coreResponseFormatter = conversationCoreResponseFormatter();
    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')
        ->once()
        ->with('020.4487.2021.0009714-69')
        ->andReturn(whatsappCoreTestPayload('search_technical_notebook', 1));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        responseFormatter: $responseFormatter,
        buildPanel: $buildPanel,
        conversationState: $stateStore,
    );

    $process(new ReceivedMessageInputDTO(
        message: '1',
        phone: '5571999999999',
    ));
    $result = $process(new ReceivedMessageInputDTO(
        message: '020.4487.2021.0009714-69',
        phone: '5571999999999',
    ));

    expect($result['intent'])->toBe('search_technical_notebook')
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

it('starts a new interaction from the main menu after a contract search completes', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();
    $coreResponseFormatter->shouldReceive('mainMenu')
        ->once()
        ->andReturn(whatsappCoreTestPayload('main_menu'));

    $contract = Mockery::mock(ContractWhatsappMessageServiceInterface::class);
    $contract->shouldReceive('menu')->once()->andReturn(whatsappCoreTestPayload('contract_menu'));
    $contract->shouldReceive('searchPrompt')->once()->with(4)->andReturn(whatsappCoreTestPayload('contract_search_prompt'));
    $contract->shouldReceive('search')
        ->once()
        ->with(4, 'Ibotirama')
        ->andReturn(whatsappCoreTestPayload('contract_summary', 1));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        conversationState: $stateStore,
        contract: $contract,
    );

    $menu = $process(new ReceivedMessageInputDTO(message: '2', phone: '5571999999999'));
    $prompt = $process(new ReceivedMessageInputDTO(message: '4', phone: '5571999999999'));
    $result = $process(new ReceivedMessageInputDTO(message: 'Ibotirama', phone: '5571999999999'));
    $newInteraction = $process(new ReceivedMessageInputDTO(message: 'Olá', phone: '5571999999999'));

    expect($menu['intent'])->toBe('contract_menu')
        ->and($prompt['intent'])->toBe('contract_search_prompt')
        ->and($result['intent'])->toBe('contract_summary')
        ->and($result['reply'])->toBe("Resposta de teste\n\n✅ Consulta concluída.")
        ->and($stateStore->get('5571999999999'))->toBeNull()
        ->and($newInteraction['intent'])->toBe('main_menu');
});

it('starts a clean build panel query after the previous query was reset', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();
    $coreResponseFormatter->shouldReceive('municipalityDisambiguation')
        ->once()
        ->with('Feira de Santana')
        ->andReturn(whatsappCoreTestPayload('municipality_disambiguation'));
    $responseFormatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $responseFormatter->shouldReceive('greeting')
        ->once()
        ->andReturn(whatsappCoreTestPayload('greeting'));

    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')
        ->once()
        ->with('Feira de Santana')
        ->andReturn(whatsappCoreTestPayload('search_technical_notebook', 1));
    $buildPanel->shouldReceive('process')
        ->once()
        ->with('Ibotirama')
        ->andReturn(whatsappCoreTestPayload('search_technical_notebook'));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        buildPanel: $buildPanel,
        responseFormatter: $responseFormatter,
        conversationState: $stateStore,
    );

    $process(new ReceivedMessageInputDTO(message: 'Feira de Santana', phone: '5571999999999'));
    $firstResult = $process(new ReceivedMessageInputDTO(message: '1', phone: '5571999999999'));
    $newPrompt = $process(new ReceivedMessageInputDTO(message: '1', phone: '5571999999999'));
    $secondResult = $process(new ReceivedMessageInputDTO(message: 'Ibotirama', phone: '5571999999999'));

    expect($firstResult['reply'])->toBe("Resposta de teste\n\n✅ Consulta concluída.")
        ->and($newPrompt['intent'])->toBe('greeting')
        ->and($secondResult['intent'])->toBe('search_technical_notebook')
        ->and($secondResult['reply'])->toBe("Resposta de teste\n\n✅ Consulta concluída.")
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

it('clears every temporary conversation field after a terminal panel query', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();

    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')
        ->once()
        ->with('Ibotirama')
        ->andReturn(whatsappCoreTestPayload('search_technical_notebook', 1));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $stateStore->put('5571999999999', new WhatsappConversationStateDTO(
        route: 'build_panel',
        municipality: 'Ibotirama',
        contractOption: 4,
    ));
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        buildPanel: $buildPanel,
        conversationState: $stateStore,
    );

    $result = $process(new ReceivedMessageInputDTO(message: 'Ibotirama', phone: '5571999999999'));

    expect($result['intent'])->toBe('search_technical_notebook')
        ->and($result['reply'])->toBe("Resposta de teste\n\n✅ Consulta concluída.")
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

it('closes the conversation and clears the state for an explicit close command', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();
    $coreResponseFormatter->shouldReceive('conversationClosed')
        ->once()
        ->andReturn(whatsappCoreTestPayload('conversation_closed'));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $stateStore->put('5571999999999', new WhatsappConversationStateDTO(route: 'contract_menu'));
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        conversationState: $stateStore,
    );

    $result = $process(new ReceivedMessageInputDTO(
        message: 'encerrar conversa',
        phone: '5571999999999',
    ));

    expect($result['intent'])->toBe('conversation_closed')
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

it('closes the conversation with a thank-you message for main menu option zero', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();
    $coreResponseFormatter->shouldReceive('conversationClosed')
        ->once()
        ->andReturn([
            'reply' => 'Consulta encerrada. Agradecemos por utilizar o Assistente da COTEC!',
            'intent' => 'conversation_closed',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);
    $coreResponseFormatter->shouldReceive('mainMenu')->never();

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        conversationState: $stateStore,
    );

    $result = $process(new ReceivedMessageInputDTO(message: '0', phone: '5571999999999'));

    expect($result['intent'])->toBe('conversation_closed')
        ->and($result['reply'])->toContain('Agradecemos por utilizar o Assistente da COTEC!')
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

it('resets after a panel query with multiple records', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();

    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')
        ->once()
        ->with('Ibotirama')
        ->andReturn(whatsappCoreTestPayload('search_technical_notebook', 2));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $stateStore->put('5571999999999', new WhatsappConversationStateDTO(route: 'build_panel'));
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        buildPanel: $buildPanel,
        conversationState: $stateStore,
    );

    $result = $process(new ReceivedMessageInputDTO(
        message: 'Ibotirama',
        phone: '5571999999999',
    ));

    expect($result['total'])->toBe(2)
        ->and($result['reply'])->toStartWith('Resposta de teste')
        ->and($result['reply'])->toBe("Resposta de teste\n\n✅ Consulta concluída.")
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

it('treats the next message as a new interaction after a panel query', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();
    $coreResponseFormatter->shouldReceive('municipalityDisambiguation')
        ->once()
        ->with('Ibotirama')
        ->andReturn(whatsappCoreTestPayload('municipality_disambiguation'));

    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')
        ->once()
        ->with('Ibotirama')
        ->andReturn(whatsappCoreTestPayload('search_technical_notebook', 1));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $stateStore->put('5571999999999', new WhatsappConversationStateDTO(route: 'build_panel'));
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        buildPanel: $buildPanel,
        conversationState: $stateStore,
    );

    $result = $process(new ReceivedMessageInputDTO(message: 'Ibotirama', phone: '5571999999999'));
    $newInteraction = $process(new ReceivedMessageInputDTO(message: 'Ibotirama', phone: '5571999999999'));

    expect($result['intent'])->toBe('search_technical_notebook')
        ->and($newInteraction['intent'])->toBe('municipality_disambiguation')
        ->and($stateStore->get('5571999999999')?->route)->toBe('municipality_disambiguation')
        ->and($stateStore->get('5571999999999')?->municipality)->toBe('Ibotirama');
});

it('resets after every terminal contract query', function (int $option, string $intent) {
    $coreResponseFormatter = conversationCoreResponseFormatter();

    $contract = Mockery::mock(ContractWhatsappMessageServiceInterface::class);
    $contract->shouldReceive('search')
        ->once()
        ->with($option, 'Salvador')
        ->andReturn(whatsappCoreTestPayload($intent, 1));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $stateStore->put('5571999999999', new WhatsappConversationStateDTO(
        route: 'contract_search',
        contractOption: $option,
    ));
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        conversationState: $stateStore,
        contract: $contract,
    );

    $result = $process(new ReceivedMessageInputDTO(
        message: 'Salvador',
        phone: '5571999999999',
    ));

    expect($result['intent'])->toBe($intent)
        ->and($result['reply'])->toBe("Resposta de teste\n\n✅ Consulta concluída.")
        ->and($stateStore->get('5571999999999'))->toBeNull();
})->with([
    'aditivos' => [1, 'contract_value_additives'],
    'reajustes' => [2, 'contract_adjustments'],
    'prazos' => [3, 'contract_execution_deadlines'],
    'resumo contratual' => [4, 'contract_summary'],
]);

it('keeps the selected panel route after an intermediate response', function () {
    $coreResponseFormatter = conversationCoreResponseFormatter();

    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')
        ->once()
        ->with('mensagem inválida')
        ->andReturn(whatsappCoreTestPayload('unknown'));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $stateStore->put('5571999999999', new WhatsappConversationStateDTO(route: 'build_panel'));
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        buildPanel: $buildPanel,
        conversationState: $stateStore,
    );

    $result = $process(new ReceivedMessageInputDTO(
        message: 'mensagem inválida',
        phone: '5571999999999',
    ));

    expect($result['intent'])->toBe('unknown')
        ->and($stateStore->get('5571999999999')?->route)->toBe('build_panel');
});

it('resets conversation state without deleting the idempotency reservation', function () {
    config(['cache.default' => 'array']);

    Cache::put('whatsapp:incoming:terminal-001', 'queued', 3600);

    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')
        ->once()
        ->with('Ibotirama')
        ->andReturn(whatsappCoreTestPayload('search_technical_notebook', 1));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $stateStore->put('5571999999999', new WhatsappConversationStateDTO(
        route: 'build_panel',
        municipality: 'Ibotirama',
        contractOption: 4,
    ));
    $process = processWhatsappConversationUsecase(
        buildPanel: $buildPanel,
        conversationState: $stateStore,
    );

    $process(new ReceivedMessageInputDTO(
        message: 'Ibotirama',
        phone: '5571999999999',
        externalId: 'terminal-001',
    ));

    expect($stateStore->get('5571999999999'))->toBeNull()
        ->and(Cache::get('whatsapp:incoming:terminal-001'))->toBe('queued');
});

it('rejects unknown municipality text without querying or storing disambiguation', function (string $message) {
    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $formatter = conversationCoreResponseFormatter();
    $formatter->shouldReceive('mainMenu')->once()->andReturn(whatsappCoreTestPayload('main_menu'));
    $formatter->shouldReceive('municipalityDisambiguation')->never();
    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')->never();
    $contract = Mockery::mock(ContractWhatsappMessageServiceInterface::class);
    $contract->shouldReceive('search')->never();
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $formatter,
        conversationState: $stateStore,
        buildPanel: $buildPanel,
        contract: $contract,
    );

    expect($process(new ReceivedMessageInputDTO(message: $message, phone: '5571999999999'))['intent'])
        ->toBe('main_menu')
        ->and($stateStore->get('5571999999999'))->toBeNull();
})->with(['Qualquer coisa', 'Entrada 1', 'teste', 'banana', 'quero consultar contrato', 'empresa qualquer', '52/2022', '020.4487.2023.0000620-96']);

it('keeps invalid municipality input in the panel without executing a search', function (string $message) {
    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $stateStore->put('5571999999999', new WhatsappConversationStateDTO(route: 'build_panel'));
    app()->instance(
        MunicipalityExtractorServiceInterface::class,
        municipalityExtractorForTests(),
    );
    $searchAdapter = Mockery::mock(WhatsappMessageSearchAdapterInterface::class);
    $searchAdapter->shouldReceive('search')->never();
    app()->instance(WhatsappMessageSearchAdapterInterface::class, $searchAdapter);
    $formatter = Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $formatter->shouldReceive('unknownIntent')->once()->andReturn(whatsappCoreTestPayload('unknown'));
    app()->instance(WhatsappMessageResponseFormatterInterface::class, $formatter);
    $process = processWhatsappConversationUsecase(
        conversationState: $stateStore,
        buildPanel: app(BuildPanelWhatsappMessageServiceInterface::class),
    );

    expect($process(new ReceivedMessageInputDTO(message: $message, phone: '5571999999999'))['intent'])
        ->toBe('unknown')
        ->and($stateStore->get('5571999999999')?->route)->toBe('build_panel')
        ->and($stateStore->get('5571999999999')?->municipality)->toBeNull();
})->with(['Qualquer coisa', 'Entrada 1', 'Teste', 'abcdef', '123']);

it('stores and queries the canonical municipality from the first message', function (string $message, string $municipality) {
    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $formatter = conversationCoreResponseFormatter();
    $formatter->shouldReceive('municipalityDisambiguation')->once()->with($municipality)
        ->andReturn(whatsappCoreTestPayload('municipality_disambiguation'));
    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')->once()->with($municipality)
        ->andReturn(whatsappCoreTestPayload('search_technical_notebook', 1));
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $formatter,
        conversationState: $stateStore,
        buildPanel: $buildPanel,
    );

    expect($process(new ReceivedMessageInputDTO(message: $message, phone: '5571999999999'))['intent'])
        ->toBe('municipality_disambiguation')
        ->and($stateStore->get('5571999999999')?->municipality)->toBe($municipality);
    expect($process(new ReceivedMessageInputDTO(message: '1', phone: '5571999999999'))['intent'])
        ->toBe('search_technical_notebook');
})->with([['Salvador', 'Salvador'], ['Feira de Santana', 'Feira de Santana'], ['Varzea Grande', 'Várzea Grande'], ['VÁRZEA GRANDE', 'Várzea Grande'], ['Varzia Grande', 'Várzea Grande']]);

function processWhatsappConversationUsecase(
    ?CoreWhatsappResponseFormatterInterface $coreResponseFormatter = null,
    ?BuildPanelWhatsappMessageServiceInterface $buildPanel = null,
    ?WhatsappMessageResponseFormatterInterface $responseFormatter = null,
    ?WhatsappConversationStateStore $conversationState = null,
    ?ContractWhatsappMessageServiceInterface $contract = null,
): ProcessWhatsappMessageUsecase {
    $responseFormatter ??= Mockery::mock(WhatsappMessageResponseFormatterInterface::class);
    $buildPanel ??= Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $contract ??= Mockery::mock(ContractWhatsappMessageServiceInterface::class);
    $coreResponseFormatter ??= conversationCoreResponseFormatter();
    $conversationState ??= new WhatsappConversationStateStore(Cache::store());

    return new ProcessWhatsappMessageUsecase(
        greetingMatcher: new GreetingMessageMatcherService,
        buildPanel: $buildPanel,
        responseFormatter: $responseFormatter,
        coreResponseFormatter: $coreResponseFormatter,
        conversationState: $conversationState,
        contract: $contract,
        municipalityExtractor: municipalityExtractorForTests(),
        seiProcessRule: new SeiProcessWhatsappMessageInterpretationRule,
    );
}

/**
 * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
 */
function whatsappCoreTestPayload(string $intent, int $total = 0): array
{
    return [
        'reply' => 'Resposta de teste',
        'intent' => $intent,
        'total' => $total,
        'data' => [],
        'filters' => [],
    ];
}

function conversationCoreResponseFormatter(): CoreWhatsappResponseFormatterInterface
{
    $formatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $formatter->shouldReceive('queryCompleted')
        ->byDefault()
        ->andReturn([
            'reply' => '✅ Consulta concluída.',
            'intent' => 'query_completed',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    return $formatter;
}

it('keeps invalid contract searches at the selected step', function (int $option, string $message) {
    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $formatter = conversationCoreResponseFormatter();
    $formatter->shouldReceive('mainMenu')->never();
    $formatter->shouldReceive('queryCompleted')->never();
    app()->instance(MunicipalityExtractorServiceInterface::class, municipalityExtractorForTests());
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $formatter,
        conversationState: $stateStore,
        contract: app(ContractWhatsappMessageServiceInterface::class),
    );

    expect($process(new ReceivedMessageInputDTO(message: '2', phone: '5571999999999'))['intent'])
        ->toBe('contract_menu');
    expect($process(new ReceivedMessageInputDTO(message: (string) $option, phone: '5571999999999'))['intent'])
        ->toBe('contract_search_prompt');

    foreach ([$message, $message] as $attempt) {
        $result = $process(new ReceivedMessageInputDTO(message: $attempt, phone: '5571999999999'));

        expect($result['intent'])->toBe('contract_unknown')
            ->and($result['reply'])->toBe((new WhatsappContractDefaultReplies)->unknownIntent())
            ->and($stateStore->get('5571999999999')?->route)->toBe('contract_search')
            ->and($stateStore->get('5571999999999')?->contractOption)->toBe($option);
    }
})->with([
    [1, 'teste'], [2, 'teste'], [3, 'teste'], [4, 'banana'],
    [4, 'Construtora XYZ'], [1, '123456'],
]);

it('shows the existing main menu options for an invalid initial numeric message', function () {
    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: new WhatsappCoreResponseFormatter(new WhatsappCoreDefaultReplies, new WhatsappCoreResponsePayloadFactory),
        conversationState: $stateStore,
    );

    $result = $process(new ReceivedMessageInputDTO(message: '123456', phone: '5571999999999'));

    expect($result['reply'])->toBe((new WhatsappCoreDefaultReplies)->invalidMainMenuOption())
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

it('allows a municipality after displaying the main menu', function (bool $storedMenu) {
    $store = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: new WhatsappCoreResponseFormatter(new WhatsappCoreDefaultReplies, new WhatsappCoreResponsePayloadFactory),
        conversationState: $store,
    );

    expect($process(new ReceivedMessageInputDTO(message: 'qualquer coisa', phone: '5571999999999'))['intent'])->toBe('main_menu');
    if ($storedMenu) {
        $store->put('5571999999999', new WhatsappConversationStateDTO(route: 'main_menu'));
    }

    expect($process(new ReceivedMessageInputDTO(message: 'Salvador', phone: '5571999999999'))['intent'])
        ->toBe('municipality_disambiguation')
        ->and($store->get('5571999999999')?->municipality)->toBe('Salvador');
})->with([false, true]);

it('keeps all non-option messages inside the quick selector', function (string $message) {
    $store = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: new WhatsappCoreResponseFormatter(new WhatsappCoreDefaultReplies, new WhatsappCoreResponsePayloadFactory),
        conversationState: $store,
    );
    $selector = $process(new ReceivedMessageInputDTO(message: 'Salvador', phone: '5571999999999'));

    $this->travel(6)->minutes(function () use ($process, $message, $selector, $store) {
        expect($process(new ReceivedMessageInputDTO(message: $message, phone: '5571999999999')))->toBe($selector)
            ->and($store->get('5571999999999')?->municipality)->toBe('Salvador');
    });
})->with(['banana', 'Feira de Santana', 'Olá', '020.4487.2021.0009714-69']);

it('gives the active contract menu priority over global interpretation', function (string $message) {
    $store = new WhatsappConversationStateStore(Cache::store());
    $store->put('5571999999999', new WhatsappConversationStateDTO(route: 'contract_menu'));
    $contract = Mockery::mock(ContractWhatsappMessageServiceInterface::class);
    $contract->shouldReceive('searchPrompt')->once()->with(0)->andReturn(whatsappCoreTestPayload('contract_unknown'));
    $process = processWhatsappConversationUsecase(conversationState: $store, contract: $contract);

    expect($process(new ReceivedMessageInputDTO(message: $message, phone: '5571999999999'))['intent'])
        ->toBe('contract_unknown')
        ->and($store->get('5571999999999')?->route)->toBe('contract_menu');
})->with(['Salvador', 'banana', 'Olá', '020.4487.2021.0009714-69']);

it('opens another quick search immediately after completing the first one', function () {
    $store = new WhatsappConversationStateStore(Cache::store());
    $panel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $panel->shouldReceive('process')->once()->with('Salvador')->andReturn(whatsappCoreTestPayload('search_technical_notebook', 1));
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: new WhatsappCoreResponseFormatter(new WhatsappCoreDefaultReplies, new WhatsappCoreResponsePayloadFactory),
        conversationState: $store,
        buildPanel: $panel,
    );
    $process(new ReceivedMessageInputDTO(message: 'Salvador', phone: '5571999999999'));
    expect($process(new ReceivedMessageInputDTO(message: '1', phone: '5571999999999'))['intent'])->toBe('search_technical_notebook')
        ->and($store->get('5571999999999'))->toBeNull();
    expect($process(new ReceivedMessageInputDTO(message: 'Feira de Santana', phone: '5571999999999'))['intent'])
        ->toBe('municipality_disambiguation')
        ->and($store->get('5571999999999')?->municipality)->toBe('Feira de Santana');
});
