<?php

use App\Core\BuildPanel\Application\Interfaces\Adapter\ReadGoogleSpreadsheetAdapterInterface;
use App\Core\BuildPanel\Application\Interfaces\Adapter\SearchGoogleSheetAdapterInterface;
use App\Core\BuildPanel\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\Core\BuildPanel\Application\Interfaces\Mapper\GoogleSheetRowMapperInterface;
use App\Core\BuildPanel\Application\Interfaces\Mapper\TechnicalNotebookSheetMapperInterface;
use App\Core\BuildPanel\Application\Interfaces\Usecase\ReadGoogleSpreadsheetUsecaseInterface;
use App\Core\BuildPanel\Application\Interfaces\Usecase\SearchGoogleSheetUsecaseInterface;
use App\Core\BuildPanel\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;
use App\Core\BuildPanel\Application\Usecase\ReadGoogleSpreadsheetUsecase;
use App\Core\BuildPanel\Application\Usecase\SearchGoogleSheetUsecase;
use App\Core\BuildPanel\Application\Usecase\SearchTechnicalNotebookUsecase;
use App\Core\BuildPanel\Domain\Repository\GoogleSheetRepositoryInterface;
use App\Core\BuildPanel\Domain\Repository\TechnicalNotebookRepositoryInterface;
use App\Core\BuildPanel\Infra\Adapter\ReadGoogleSpreadsheetAdapter;
use App\Core\BuildPanel\Infra\Adapter\SearchGoogleSheetAdapter;
use App\Core\BuildPanel\Infra\Adapter\SearchTechnicalNotebookAdapter;
use App\Core\BuildPanel\Infra\Adapter\TechnicalNotebookWhatsappSearchHandler;
use App\Core\BuildPanel\Infra\Mapper\GoogleSheetRowMapper;
use App\Core\BuildPanel\Infra\Mapper\TechnicalNotebookSheetMapper;
use App\Core\BuildPanel\Infra\Message\TechnicalNotebookFoundRecordsReplyBuilder;
use App\Core\BuildPanel\Infra\Message\WhatsappDefaultReplies;
use App\Core\BuildPanel\Infra\Providers\BuildPanelServiceProvider;
use App\Core\BuildPanel\Infra\Repository\Gateway\GoogleSheetGateway;
use App\Core\BuildPanel\Infra\Repository\Gateway\TechnicalNotebookGoogleSheetGatewayRepository;
use App\Core\Conversation\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\Core\Conversation\Application\Interfaces\Repository\ConversationStateRepositoryInterface;
use App\Core\Conversation\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\Core\Conversation\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\Core\Conversation\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\WhatsappDefaultRepliesInterface;
use App\Core\Conversation\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Conversation\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Conversation\Application\Rules\MunicipalityWhatsappMessageInterpretationRule;
use App\Core\Conversation\Application\Rules\SeiProcessWhatsappMessageInterpretationRule;
use App\Core\Conversation\Application\Service\AcceptedWhatsappMessageInterpretationService;
use App\Core\Conversation\Application\Service\DirectWhatsappMessageInterpreterService;
use App\Core\Conversation\Application\Service\GreetingMessageMatcherService;
use App\Core\Conversation\Application\Service\MunicipalityExtractorService;
use App\Core\Conversation\Application\Service\ResolveWhatsappMessageInterpretationService;
use App\Core\Conversation\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Conversation\Infra\Adapter\WhatsappMessageSearchAdapter;
use App\Core\Conversation\Infra\Providers\CoreServiceProvider;
use App\Core\Conversation\Infra\Repository\CacheConversationStateRepository;
use App\Core\Conversation\Infra\Service\WhatsappMessageResponseFormatter;
use App\Core\TravelReport\Application\Interface\Adapter\DeleteTravelReportAdapterInterface;
use App\Core\TravelReport\Application\Interface\Adapter\FindTravelReportBySeiProcessAdapterInterface;
use App\Core\TravelReport\Application\Interface\Adapter\ListTravelReportByMunicipalityIdAdapterInterface;
use App\Core\TravelReport\Application\Interface\Adapter\ListTravelReportsAdapterInterface;
use App\Core\TravelReport\Application\Interface\Adapter\PersistTravelReportAdapterInterface;
use App\Core\TravelReport\Application\Interface\Usecase\DeleteTravelReportUsecaseInterface;
use App\Core\TravelReport\Application\Interface\Usecase\FindTravelReportBySeiProcessUsecaseInterface;
use App\Core\TravelReport\Application\Interface\Usecase\ListTravelReportByMunicipalityIdUsecaseInterface;
use App\Core\TravelReport\Application\Interface\Usecase\ListTravelReportsUsecaseInterface;
use App\Core\TravelReport\Application\Interface\Usecase\PersistTravelReportUsecaseInterface;
use App\Core\TravelReport\Application\Usecase\DeleteTravelReportUsecase;
use App\Core\TravelReport\Application\Usecase\FindTravelReportBySeiProcessUsecase;
use App\Core\TravelReport\Application\Usecase\ListTravelReportByMunicipalityIdUsecase;
use App\Core\TravelReport\Application\Usecase\ListTravelReportsUsecase;
use App\Core\TravelReport\Application\Usecase\PersistTravelReportUsecase;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;
use App\Core\TravelReport\Infra\Adapter\DeleteTravelReportAdapter;
use App\Core\TravelReport\Infra\Adapter\FindTravelReportBySeiProcessAdapter;
use App\Core\TravelReport\Infra\Adapter\ListTravelReportByMunicipalityIdAdapter;
use App\Core\TravelReport\Infra\Adapter\ListTravelReportsAdapter;
use App\Core\TravelReport\Infra\Adapter\PersistTravelReportAdapter;
use App\Core\TravelReport\Infra\Providers\TravelReportServiceProvider;
use App\Core\TravelReport\Infra\Repository\Gateway\TravelReportGatewayRepository;
use App\Providers\AppServiceProvider;

it('registers application module service providers', function () {
    $providers = require base_path('bootstrap/providers.php');

    expect($providers)->toContain(
        AppServiceProvider::class,
        BuildPanelServiceProvider::class,
        CoreServiceProvider::class,
        TravelReportServiceProvider::class,
    );
});

it('resolves application bindings from the container', function (
    string $abstract,
    string $concrete,
) {
    expect(app($abstract))->toBeInstanceOf($concrete);
})->with([
    [ReadGoogleSpreadsheetAdapterInterface::class, ReadGoogleSpreadsheetAdapter::class],
    [SearchGoogleSheetAdapterInterface::class, SearchGoogleSheetAdapter::class],
    [SearchTechnicalNotebookAdapterInterface::class, SearchTechnicalNotebookAdapter::class],
    [WhatsappMessageSearchAdapterInterface::class, WhatsappMessageSearchAdapter::class],
    [ReadGoogleSpreadsheetUsecaseInterface::class, ReadGoogleSpreadsheetUsecase::class],
    [SearchGoogleSheetUsecaseInterface::class, SearchGoogleSheetUsecase::class],
    [SearchTechnicalNotebookUsecaseInterface::class, SearchTechnicalNotebookUsecase::class],
    [ProcessWhatsappMessageUsecaseInterface::class, ProcessWhatsappMessageUsecase::class],
    [GoogleSheetRowMapperInterface::class, GoogleSheetRowMapper::class],
    [TechnicalNotebookSheetMapperInterface::class, TechnicalNotebookSheetMapper::class],
    [GoogleSheetRepositoryInterface::class, GoogleSheetGateway::class],
    [TechnicalNotebookRepositoryInterface::class, TechnicalNotebookGoogleSheetGatewayRepository::class],
    [TravelReportRepositoryInterface::class, TravelReportGatewayRepository::class],
    [PersistTravelReportAdapterInterface::class, PersistTravelReportAdapter::class],
    [ListTravelReportsAdapterInterface::class, ListTravelReportsAdapter::class],
    [ListTravelReportByMunicipalityIdAdapterInterface::class, ListTravelReportByMunicipalityIdAdapter::class],
    [FindTravelReportBySeiProcessAdapterInterface::class, FindTravelReportBySeiProcessAdapter::class],
    [DeleteTravelReportAdapterInterface::class, DeleteTravelReportAdapter::class],
    [PersistTravelReportUsecaseInterface::class, PersistTravelReportUsecase::class],
    [ListTravelReportsUsecaseInterface::class, ListTravelReportsUsecase::class],
    [ListTravelReportByMunicipalityIdUsecaseInterface::class, ListTravelReportByMunicipalityIdUsecase::class],
    [FindTravelReportBySeiProcessUsecaseInterface::class, FindTravelReportBySeiProcessUsecase::class],
    [DeleteTravelReportUsecaseInterface::class, DeleteTravelReportUsecase::class],
    [WhatsappDefaultRepliesInterface::class, WhatsappDefaultReplies::class],
    [GreetingMessageMatcherServiceInterface::class, GreetingMessageMatcherService::class],
    [MunicipalityExtractorServiceInterface::class, MunicipalityExtractorService::class],
    [SeiProcessWhatsappMessageInterpretationRuleInterface::class, SeiProcessWhatsappMessageInterpretationRule::class],
    [WhatsappMessageInterpretationRuleInterface::class, MunicipalityWhatsappMessageInterpretationRule::class],
    [DirectWhatsappMessageInterpreterServiceInterface::class, DirectWhatsappMessageInterpreterService::class],
    [ResolveWhatsappMessageInterpretationServiceInterface::class, ResolveWhatsappMessageInterpretationService::class],
    [AcceptedWhatsappMessageInterpretationServiceInterface::class, AcceptedWhatsappMessageInterpretationService::class],
    [ConversationStateRepositoryInterface::class, CacheConversationStateRepository::class],
    [WhatsappMessageResponseFormatterInterface::class, WhatsappMessageResponseFormatter::class],
]);

it('registers build panel whatsapp extension handlers', function () {
    $searchHandlers = collect(app()->tagged('whatsapp.search_handlers'))->all();
    $replyBuilders = collect(app()->tagged('whatsapp.found_records_reply_builders'))->all();

    expect($searchHandlers)
        ->toHaveCount(1)
        ->and($searchHandlers[0])->toBeInstanceOf(TechnicalNotebookWhatsappSearchHandler::class)
        ->and($replyBuilders)
        ->toHaveCount(1)
        ->and($replyBuilders[0])->toBeInstanceOf(TechnicalNotebookFoundRecordsReplyBuilder::class);
});

it('resolves direct whatsapp interpreter with bound interpretation rules', function () {
    $interpretation = app(DirectWhatsappMessageInterpreterServiceInterface::class)->interpret(
        'Quero consultar o processo 020.4487.2021.0009714-69',
    );

    expect($interpretation)
        ->not->toBeNull()
        ->and($interpretation->filters)->toBe([
            'process' => '020.4487.2021.0009714-69',
        ]);

    $municipalityInterpretation = app(DirectWhatsappMessageInterpreterServiceInterface::class)->interpret('Bom dia, ANDARAÍ');

    expect($municipalityInterpretation)
        ->not->toBeNull()
        ->and($municipalityInterpretation->filters)->toBe([
            'municipality' => 'ANDARAÍ',
        ]);
});
