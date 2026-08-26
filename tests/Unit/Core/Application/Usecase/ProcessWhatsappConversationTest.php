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
        ->toContain('2️⃣ Resumo das informações de contratos do município');
});

it('resolves the integrated whatsapp usecase from the application container', function () {
    expect(app(ProcessWhatsappMessageUsecaseInterface::class))
        ->toBeInstanceOf(ProcessWhatsappMessageUsecase::class);
});

it('stores a direct municipality and routes the selected contract summary without asking for it again', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
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
        ->and($stateStore->get('5571999999999')?->route)->toBe('contract_menu');
});

it('routes the selected municipality extract to the build panel using the stored municipality', function () {
    $coreResponseFormatter = Mockery::mock(CoreWhatsappResponseFormatterInterface::class);
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
        ->and($stateStore->get('5571999999999'))->toBeNull();
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

    $buildPanel = Mockery::mock(BuildPanelWhatsappMessageServiceInterface::class);
    $buildPanel->shouldReceive('process')
        ->once()
        ->with('020.4487.2021.0009714-69')
        ->andReturn(whatsappCoreTestPayload('search_technical_notebook', 1));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
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

it('opens the contract menu and keeps the user in that module after a search', function () {
    $contract = Mockery::mock(ContractWhatsappMessageServiceInterface::class);
    $contract->shouldReceive('menu')->once()->andReturn(whatsappCoreTestPayload('contract_menu'));
    $contract->shouldReceive('searchPrompt')->once()->with(4)->andReturn(whatsappCoreTestPayload('contract_search_prompt'));
    $contract->shouldReceive('search')->once()->with(4, 'Ibotirama')->andReturn(whatsappCoreTestPayload('contract_summary', 1));

    $stateStore = new WhatsappConversationStateStore(Cache::store());
    $process = processWhatsappConversationUsecase(
        conversationState: $stateStore,
        contract: $contract,
    );

    $menu = $process(new ReceivedMessageInputDTO(message: '2', phone: '5571999999999'));
    $prompt = $process(new ReceivedMessageInputDTO(message: '4', phone: '5571999999999'));
    $result = $process(new ReceivedMessageInputDTO(message: 'Ibotirama', phone: '5571999999999'));

    expect($menu['intent'])->toBe('contract_menu')
        ->and($prompt['intent'])->toBe('contract_search_prompt')
        ->and($result['intent'])->toBe('contract_summary')
        ->and($stateStore->get('5571999999999')?->route)->toBe('contract_menu');
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
