<?php

use App\BuildPanel\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\BuildPanel\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\BuildPanel\Application\Interfaces\Mapper\TechnicalNotebookSheetMapperInterface;
use App\BuildPanel\Application\Interfaces\Parser\WhatsappMessageInterpretationParserInterface;
use App\BuildPanel\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\BuildPanel\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\BuildPanel\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\BuildPanelWhatsappMessageServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\InterpretWhatsappMessageWithAiServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\BuildPanel\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;
use App\BuildPanel\Application\Rules\MunicipalityWhatsappMessageInterpretationRule;
use App\BuildPanel\Application\Rules\SeiProcessWhatsappMessageInterpretationRule;
use App\BuildPanel\Application\Service\AcceptedWhatsappMessageInterpretationService;
use App\BuildPanel\Application\Service\BuildPanelWhatsappMessageService;
use App\BuildPanel\Application\Service\DirectWhatsappMessageInterpreterService;
use App\BuildPanel\Application\Service\MunicipalityExtractorService;
use App\BuildPanel\Application\Service\ResolveWhatsappMessageInterpretationService;
use App\BuildPanel\Application\Usecase\SearchTechnicalNotebookUsecase;
use App\BuildPanel\Domain\Repository\TechnicalNotebookRepositoryInterface;
use App\BuildPanel\Infra\Adapter\SearchTechnicalNotebookAdapter;
use App\BuildPanel\Infra\Adapter\WhatsappMessageSearchAdapter;
use App\BuildPanel\Infra\Mapper\TechnicalNotebookSheetMapper;
use App\BuildPanel\Infra\Parser\WhatsappMessageInterpretationParser;
use App\BuildPanel\Infra\Repository\Gateway\TechnicalNotebookGoogleSheetGatewayRepository;
use App\BuildPanel\Infra\Service\InterpretWhatsappMessageWithAiService;
use App\BuildPanel\Infra\Service\WhatsappMessageResponseFormatter;
use App\Core\Application\Factory\MessageFactory;
use App\Core\Application\Interfaces\Adapter\ReadGoogleSpreadsheetAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchGoogleSheetAdapterInterface;
use App\Core\Application\Interfaces\Adapter\WhatsappWebhookPayloadAdapterInterface;
use App\Core\Application\Interfaces\Factory\MessageFactoryInterface;
use App\Core\Application\Interfaces\Mapper\GoogleSheetRowMapperInterface;
use App\Core\Application\Interfaces\Mapper\WhatsappWebhookPayloadMapperInterface;
use App\Core\Application\Interfaces\Message\WhatsappMainMenuMessageBuilderInterface;
use App\Core\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Application\Interfaces\Service\LogWhatsappMessageSenderInterface;
use App\Core\Application\Interfaces\Service\MessageIntentResolverInterface;
use App\Core\Application\Interfaces\Service\WhatsappBuildPanelFlowServiceInterface;
use App\Core\Application\Interfaces\Service\WhatsappConversationFlowServiceInterface;
use App\Core\Application\Interfaces\Service\WhatsappMainMenuServiceInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageProcessorInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageSenderInterface;
use App\Core\Application\Interfaces\Usecase\AcceptIncomingWhatsappWebhookUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\ProcessIncomingWhatsappWebhookUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\ReadGoogleSpreadsheetUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\SearchGoogleSheetUsecaseInterface;
use App\Core\Application\Service\GreetingMessageMatcherService;
use App\Core\Application\Service\WhatsappBuildPanelFlowService;
use App\Core\Application\Service\WhatsappConversationFlowService;
use App\Core\Application\Service\WhatsappMainMenuService;
use App\Core\Application\Service\WhatsappMessageProcessorService;
use App\Core\Application\Usecase\AcceptIncomingWhatsappWebhookUsecase;
use App\Core\Application\Usecase\ProcessIncomingWhatsappWebhookUsecase;
use App\Core\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Application\Usecase\ReadGoogleSpreadsheetUsecase;
use App\Core\Application\Usecase\SearchGoogleSheetUsecase;
use App\Core\Domain\Repository\GoogleSheetRepositoryInterface;
use App\Core\Domain\Repository\WhatsappConversationStateRepositoryInterface;
use App\Core\Domain\Resolver\MessageIntentResolver;
use App\Core\Infra\Adapter\ReadGoogleSpreadsheetAdapter;
use App\Core\Infra\Adapter\SearchGoogleSheetAdapter;
use App\Core\Infra\Adapter\WhatsappWebhookPayloadAdapter;
use App\Core\Infra\External\EditaCodigoWhatsappMessageSender;
use App\Core\Infra\External\LogWhatsappMessageSender;
use App\Core\Infra\Mapper\GoogleSheetRowMapper;
use App\Core\Infra\Mapper\WhatsappWebhookPayloadMapper;
use App\Core\Infra\Message\WhatsappMainMenuMessageBuilder;
use App\Core\Infra\Repository\EloquentRepository\CacheWhatsappConversationStateRepository;
use App\Core\Infra\Repository\Gateway\GoogleSheetGateway;
use App\TechnicalInspectionReport\Application\Factory\RegisterTechnicalInspectionReportCatalogInputDTOFactory;
use App\TechnicalInspectionReport\Application\Factory\TechnicalInspectionReportGoogleSheetFactory;
use App\TechnicalInspectionReport\Application\Interfaces\Factory\RegisterTechnicalInspectionReportCatalogInputDTOFactoryInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Factory\TechnicalInspectionReportGoogleSheetFactoryInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Storage\TechnicalInspectionReportFileStorageInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\FindTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Application\Usecase\FindTechnicalInspectionReportUsecase;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportDriveRepositoryInterface;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportSheetRepositoryInterface;
use App\TechnicalInspectionReport\Infra\External\GoogleDriveTechnicalInspectionReportFileStorage;
use App\TechnicalInspectionReport\Infra\Repository\Gateway\TechnicalInspectionReportGoogleDriveGatewayRepository;
use App\TechnicalInspectionReport\Infra\Repository\Gateway\TechnicalInspectionReportGoogleSheetGatewayRepository;

it('resolves application bindings from the container', function (
    string $abstract,
    string $concrete,
) {
    expect(app($abstract))->toBeInstanceOf($concrete);
})->with([
    [ReadGoogleSpreadsheetAdapterInterface::class, ReadGoogleSpreadsheetAdapter::class],
    [ReadGoogleSpreadsheetUsecaseInterface::class, ReadGoogleSpreadsheetUsecase::class],
    [SearchGoogleSheetAdapterInterface::class, SearchGoogleSheetAdapter::class],
    [SearchGoogleSheetUsecaseInterface::class, SearchGoogleSheetUsecase::class],
    [SearchTechnicalNotebookAdapterInterface::class, SearchTechnicalNotebookAdapter::class],
    [SearchTechnicalNotebookUsecaseInterface::class, SearchTechnicalNotebookUsecase::class],
    [WhatsappMessageSearchAdapterInterface::class, WhatsappMessageSearchAdapter::class],
    [WhatsappWebhookPayloadAdapterInterface::class, WhatsappWebhookPayloadAdapter::class],
    [ProcessWhatsappMessageUsecaseInterface::class, ProcessWhatsappMessageUsecase::class],
    [AcceptIncomingWhatsappWebhookUsecaseInterface::class, AcceptIncomingWhatsappWebhookUsecase::class],
    [ProcessIncomingWhatsappWebhookUsecaseInterface::class, ProcessIncomingWhatsappWebhookUsecase::class],
    [TechnicalNotebookSheetMapperInterface::class, TechnicalNotebookSheetMapper::class],
    [GoogleSheetRowMapperInterface::class, GoogleSheetRowMapper::class],
    [WhatsappWebhookPayloadMapperInterface::class, WhatsappWebhookPayloadMapper::class],
    [GoogleSheetRepositoryInterface::class, GoogleSheetGateway::class],
    [TechnicalNotebookRepositoryInterface::class, TechnicalNotebookGoogleSheetGatewayRepository::class],
    [WhatsappConversationStateRepositoryInterface::class, CacheWhatsappConversationStateRepository::class],
    [GreetingMessageMatcherServiceInterface::class, GreetingMessageMatcherService::class],
    [MunicipalityExtractorServiceInterface::class, MunicipalityExtractorService::class],
    [SeiProcessWhatsappMessageInterpretationRuleInterface::class, SeiProcessWhatsappMessageInterpretationRule::class],
    [WhatsappMessageInterpretationRuleInterface::class, MunicipalityWhatsappMessageInterpretationRule::class],
    [DirectWhatsappMessageInterpreterServiceInterface::class, DirectWhatsappMessageInterpreterService::class],
    [ResolveWhatsappMessageInterpretationServiceInterface::class, ResolveWhatsappMessageInterpretationService::class],
    [AcceptedWhatsappMessageInterpretationServiceInterface::class, AcceptedWhatsappMessageInterpretationService::class],
    [BuildPanelWhatsappMessageServiceInterface::class, BuildPanelWhatsappMessageService::class],
    [InterpretWhatsappMessageWithAiServiceInterface::class, InterpretWhatsappMessageWithAiService::class],
    [WhatsappMessageInterpretationParserInterface::class, WhatsappMessageInterpretationParser::class],
    [LogWhatsappMessageSenderInterface::class, LogWhatsappMessageSender::class],
    [WhatsappMessageResponseFormatterInterface::class, WhatsappMessageResponseFormatter::class],
    [MessageFactoryInterface::class, MessageFactory::class],
    [MessageIntentResolverInterface::class, MessageIntentResolver::class],
    [WhatsappMessageProcessorInterface::class, WhatsappMessageProcessorService::class],
    [WhatsappBuildPanelFlowServiceInterface::class, WhatsappBuildPanelFlowService::class],
    [WhatsappConversationFlowServiceInterface::class, WhatsappConversationFlowService::class],
    [WhatsappMainMenuServiceInterface::class, WhatsappMainMenuService::class],
    [WhatsappMainMenuMessageBuilderInterface::class, WhatsappMainMenuMessageBuilder::class],
    [TechnicalInspectionReportGoogleSheetFactoryInterface::class, TechnicalInspectionReportGoogleSheetFactory::class],
    [RegisterTechnicalInspectionReportCatalogInputDTOFactoryInterface::class, RegisterTechnicalInspectionReportCatalogInputDTOFactory::class],
    [FindTechnicalInspectionReportUsecaseInterface::class, FindTechnicalInspectionReportUsecase::class],
    [TechnicalInspectionReportFileStorageInterface::class, GoogleDriveTechnicalInspectionReportFileStorage::class],
    [TechnicalInspectionReportSheetRepositoryInterface::class, TechnicalInspectionReportGoogleSheetGatewayRepository::class],
    [TechnicalInspectionReportDriveRepositoryInterface::class, TechnicalInspectionReportGoogleDriveGatewayRepository::class],
]);

it('resolves the log whatsapp sender when the local sender is configured', function () {
    config(['services.whatsapp.sender' => 'log']);

    expect(app(WhatsappMessageSenderInterface::class))->toBeInstanceOf(LogWhatsappMessageSender::class);
});

it('resolves the EditaCodigo whatsapp sender when the EditaCodigo sender is configured', function () {
    config(['services.whatsapp.sender' => 'editacodigo']);

    expect(app(WhatsappMessageSenderInterface::class))->toBeInstanceOf(EditaCodigoWhatsappMessageSender::class);
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
