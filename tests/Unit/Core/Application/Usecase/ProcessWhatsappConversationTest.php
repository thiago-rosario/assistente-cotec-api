<?php

use App\BuildPanel\Application\Rules\SeiProcessWhatsappMessageInterpretationRule;
use App\BuildPanel\Application\Service\MunicipalityExtractorService;
use App\Contract\Application\Interfaces\Service\ContractWhatsappMessageServiceInterface;
use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\DTO\WhatsappConversationStateDTO;
use App\Core\Application\Interfaces\Service\BuildPanelWhatsappMessageServiceInterface;
use App\Core\Application\Interfaces\Service\CoreWhatsappResponseFormatterInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Service\GreetingMessageMatcherService;
use App\Core\Application\Usecase\ProcessWhatsappMessageUsecase;
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

it('opens the main menu for greetings and clears the previous conversation state', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $coreResponseFormatter->shouldReceive('mainMenu')->once()->andReturn(whatsappCoreTestPayload('main_menu'));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $stateStore->put('5571999999999', new WhatsappConversationStateDTO(route: 'contract_menu'));

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

    expect($formatter->postQueryAction()['reply'])->toBe(
        "✅ Consulta concluída.\n\n"
        ."Deseja realizar outra consulta?\n\n"
        ."1️⃣ Realizar nova consulta\n"
        ."0️⃣ Voltar ao menu principal\n\n"
        .'Digite apenas o número da opção desejada.',
    );

    expect($formatter->invalidPostQueryAction()['reply'])
        ->toStartWith('Opção inválida.')
        ->toContain('1️⃣ Realizar nova consulta')
        ->toContain('0️⃣ Voltar ao menu principal')
        ->not->toContain('2️⃣');

    expect($formatter->conversationClosed()['reply'])
        ->toContain('Consulta encerrada.')
        ->toContain('Agradecemos por utilizar o Assistente da COTEC!');
});

it('resolves the integrated whatsapp usecase from the application container', function () {
    expect(app(ProcessWhatsappMessageUsecaseInterface::class))
        ->toBeInstanceOf(ProcessWhatsappMessageUsecase::class);
});

it('stores a direct municipality and asks what to do after the contract summary', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $coreResponseFormatter->shouldReceive('municipalityDisambiguation')
        ->once()
        ->with('Ibotirama')
        ->andReturn(whatsappCoreTestPayload('municipality_disambiguation'));
    $coreResponseFormatter->shouldReceive('postQueryAction')
        ->once()
        ->andReturn(whatsappPostQueryTestPayload());

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
        ->and($secondResult['reply'])->toContain('Pergunta pós-consulta')
        ->and($stateStore->get('5571999999999')?->route)->toBe('post_query_action')
        ->and($stateStore->get('5571999999999')?->contractOption)->toBe(4);
});

it('routes the selected municipality extract to the build panel using the stored municipality', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $coreResponseFormatter->shouldReceive('municipalityDisambiguation')
        ->once()
        ->with('Feira de Santana')
        ->andReturn(whatsappCoreTestPayload('municipality_disambiguation'));
    $coreResponseFormatter->shouldReceive('postQueryAction')
        ->once()
        ->andReturn(whatsappPostQueryTestPayload());

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
        ->and($result['reply'])->toContain('Pergunta pós-consulta')
        ->and($stateStore->get('5571999999999')?->route)->toBe('post_query_action')
        ->and($stateStore->get('5571999999999')?->municipality)->toBeNull();
});

it('returns the main menu for a standalone sei process until the panel is selected', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
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

    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $coreResponseFormatter->shouldReceive('postQueryAction')
        ->once()
        ->andReturn(whatsappPostQueryTestPayload());

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
        ->and($stateStore->get('5571999999999')?->route)->toBe('post_query_action');
});

it('opens the contract menu and asks what to do after a search', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $coreResponseFormatter->shouldReceive('postQueryAction')
        ->twice()
        ->andReturn(whatsappPostQueryTestPayload());

    $contract = Mockery::mock(ContractWhatsappMessageServiceInterface::class);
    $contract->shouldReceive('menu')->once()->andReturn(whatsappCoreTestPayload('contract_menu'));
    $contract->shouldReceive('searchPrompt')->twice()->with(4)->andReturn(whatsappCoreTestPayload('contract_search_prompt'));
    $contract->shouldReceive('search')
        ->once()
        ->with(4, 'Ibotirama')
        ->andReturn(whatsappCoreTestPayload('contract_summary', 1));
    $contract->shouldReceive('search')
        ->once()
        ->with(4, 'Salvador')
        ->andReturn(whatsappCoreTestPayload('contract_summary'));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
        coreResponseFormatter: $coreResponseFormatter,
        conversationState: $stateStore,
        contract: $contract,
    );

    $menu = $process(new ReceivedMessageInputDTO(message: '2', phone: '5571999999999'));
    $prompt = $process(new ReceivedMessageInputDTO(message: '4', phone: '5571999999999'));
    $result = $process(new ReceivedMessageInputDTO(message: 'Ibotirama', phone: '5571999999999'));
    $newPrompt = $process(new ReceivedMessageInputDTO(message: '1', phone: '5571999999999'));
    $newResult = $process(new ReceivedMessageInputDTO(message: 'Salvador', phone: '5571999999999'));

    expect($menu['intent'])->toBe('contract_menu')
        ->and($prompt['intent'])->toBe('contract_search_prompt')
        ->and($result['intent'])->toBe('contract_summary')
        ->and($result['reply'])->toContain('Pergunta pós-consulta')
        ->and($newPrompt['intent'])->toBe('contract_search_prompt')
        ->and($newResult['intent'])->toBe('contract_summary')
        ->and($newResult['total'])->toBe(0)
        ->and($newResult['reply'])->toContain('Pergunta pós-consulta')
        ->and($stateStore->get('5571999999999')?->route)->toBe('post_query_action')
        ->and($stateStore->get('5571999999999')?->contractOption)->toBe(4);
});

it('starts a clean build panel query after choosing a new post-query action', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $coreResponseFormatter->shouldReceive('municipalityDisambiguation')
        ->once()
        ->with('Feira de Santana')
        ->andReturn(whatsappCoreTestPayload('municipality_disambiguation'));
    $coreResponseFormatter->shouldReceive('postQueryAction')
        ->twice()
        ->andReturn(whatsappPostQueryTestPayload());

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

    expect($firstResult['reply'])->toContain('Pergunta pós-consulta')
        ->and($newPrompt['intent'])->toBe('greeting')
        ->and($secondResult['intent'])->toBe('search_technical_notebook')
        ->and($secondResult['reply'])->toContain('Pergunta pós-consulta')
        ->and($stateStore->get('5571999999999')?->route)->toBe('post_query_action')
        ->and($stateStore->get('5571999999999')?->municipality)->toBeNull()
        ->and($stateStore->get('5571999999999')?->contractOption)->toBeNull();
});

it('returns to the main menu and clears the state for post-query option zero', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $coreResponseFormatter->shouldReceive('postQueryAction')
        ->once()
        ->andReturn(whatsappPostQueryTestPayload());
    $coreResponseFormatter->shouldReceive('mainMenu')
        ->once()
        ->andReturn(whatsappCoreTestPayload('main_menu'));

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

    $process(new ReceivedMessageInputDTO(message: 'Ibotirama', phone: '5571999999999'));
    $result = $process(new ReceivedMessageInputDTO(message: '0', phone: '5571999999999'));

    expect($result['intent'])->toBe('main_menu')
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

it('closes the conversation and clears the state for an explicit close command', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
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
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
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

it('asks for a post-query action after multiple panel records', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $coreResponseFormatter->shouldReceive('postQueryAction')
        ->once()
        ->andReturn(whatsappPostQueryTestPayload());

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
        ->and($result['reply'])->toContain('Pergunta pós-consulta')
        ->and($stateStore->get('5571999999999')?->route)->toBe('post_query_action');
});

it('rejects post-query content without searching and keeps only options one and zero', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $coreResponseFormatter->shouldReceive('postQueryAction')
        ->once()
        ->andReturn(whatsappPostQueryTestPayload());
    $coreResponseFormatter->shouldReceive('invalidPostQueryAction')
        ->once()
        ->andReturn([
            'reply' => 'Opção inválida.\n\n1️⃣ Realizar nova consulta\n0️⃣ Voltar ao menu principal',
            'intent' => 'invalid_post_query_action',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

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

    $process(new ReceivedMessageInputDTO(message: 'Ibotirama', phone: '5571999999999'));
    $result = $process(new ReceivedMessageInputDTO(message: 'Ibotirama', phone: '5571999999999'));

    expect($result['intent'])->toBe('invalid_post_query_action')
        ->and($result['reply'])->toContain('1️⃣ Realizar nova consulta')
        ->and($result['reply'])->toContain('0️⃣ Voltar ao menu principal')
        ->and($stateStore->get('5571999999999')?->route)->toBe('post_query_action');
});

it('does not ask for a post-query action after an unsuccessful query response', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $coreResponseFormatter->shouldReceive('postQueryAction')->never();

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
        ->and($stateStore->get('5571999999999'))->toBeNull();
});

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
    $coreResponseFormatter ??= Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
    $conversationState ??= new WhatsappConversationStateStore(Cache::store());

    return new ProcessWhatsappMessageUsecase(
        greetingMatcher: new GreetingMessageMatcherService,
        buildPanel: $buildPanel,
        responseFormatter: $responseFormatter,
        coreResponseFormatter: $coreResponseFormatter,
        conversationState: $conversationState,
        contract: $contract,
        municipalityExtractor: new MunicipalityExtractorService,
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

/**
 * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
 */
function whatsappPostQueryTestPayload(): array
{
    return [
        'reply' => 'Pergunta pós-consulta',
        'intent' => 'post_query_action',
        'total' => 0,
        'data' => [],
        'filters' => [],
    ];
}
