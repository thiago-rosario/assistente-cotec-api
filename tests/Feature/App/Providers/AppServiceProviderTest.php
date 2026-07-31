<?php

use App\BuildPanel\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\BuildPanel\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\BuildPanel\Application\Interfaces\Mapper\TechnicalNotebookSheetMapperInterface;
use App\BuildPanel\Application\Interfaces\Parser\WhatsappMessageInterpretationParserInterface;
use App\BuildPanel\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\BuildPanel\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\BuildPanel\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\InterpretWhatsappMessageWithAiServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
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
use App\Core\Application\Interfaces\Adapter\ReadGoogleSpreadsheetAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchGoogleSheetAdapterInterface;
use App\Core\Application\Interfaces\Adapter\WhatsappWebhookPayloadAdapterInterface;
use App\Core\Application\Interfaces\Mapper\GoogleSheetRowMapperInterface;
use App\Core\Application\Interfaces\Mapper\WhatsappWebhookPayloadMapperInterface;
use App\Core\Application\Interfaces\Service\BuildPanelWhatsappMessageServiceInterface;
use App\Core\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageSenderInterface;
use App\Core\Application\Interfaces\Usecase\AcceptIncomingWhatsappWebhookUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\ProcessIncomingWhatsappWebhookUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\ReadGoogleSpreadsheetUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\SearchGoogleSheetUsecaseInterface;
use App\Core\Application\Service\GreetingMessageMatcherService;
use App\Core\Application\Usecase\AcceptIncomingWhatsappWebhookUsecase;
use App\Core\Application\Usecase\ProcessIncomingWhatsappWebhookUsecase;
use App\Core\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Application\Usecase\ReadGoogleSpreadsheetUsecase;
use App\Core\Application\Usecase\SearchGoogleSheetUsecase;
use App\Core\Domain\Repository\GoogleSheetRepositoryInterface;
use App\Core\Infra\Adapter\ReadGoogleSpreadsheetAdapter;
use App\Core\Infra\Adapter\SearchGoogleSheetAdapter;
use App\Core\Infra\Adapter\WhatsappWebhookPayloadAdapter;
use App\Core\Infra\External\EditaCodigoWhatsappMessageSender;
use App\Core\Infra\Mapper\GoogleSheetRowMapper;
use App\Core\Infra\Mapper\WhatsappWebhookPayloadMapper;
use App\Core\Infra\Repository\Gateway\GoogleSheetGateway;

it('resolves core and build panel bindings from their module providers', function (
    string $abstract,
    string $concrete,
) {
    expect(app($abstract))->toBeInstanceOf($concrete);
})->with([
    [ReadGoogleSpreadsheetAdapterInterface::class, ReadGoogleSpreadsheetAdapter::class],
    [ReadGoogleSpreadsheetUsecaseInterface::class, ReadGoogleSpreadsheetUsecase::class],
    [SearchGoogleSheetAdapterInterface::class, SearchGoogleSheetAdapter::class],
    [SearchGoogleSheetUsecaseInterface::class, SearchGoogleSheetUsecase::class],
    [WhatsappWebhookPayloadAdapterInterface::class, WhatsappWebhookPayloadAdapter::class],
    [GoogleSheetRowMapperInterface::class, GoogleSheetRowMapper::class],
    [WhatsappWebhookPayloadMapperInterface::class, WhatsappWebhookPayloadMapper::class],
    [GoogleSheetRepositoryInterface::class, GoogleSheetGateway::class],
    [ProcessWhatsappMessageUsecaseInterface::class, ProcessWhatsappMessageUsecase::class],
    [AcceptIncomingWhatsappWebhookUsecaseInterface::class, AcceptIncomingWhatsappWebhookUsecase::class],
    [ProcessIncomingWhatsappWebhookUsecaseInterface::class, ProcessIncomingWhatsappWebhookUsecase::class],
    [GreetingMessageMatcherServiceInterface::class, GreetingMessageMatcherService::class],
    [WhatsappMessageSenderInterface::class, EditaCodigoWhatsappMessageSender::class],
    [BuildPanelWhatsappMessageServiceInterface::class, BuildPanelWhatsappMessageService::class],
    [WhatsappMessageResponseFormatterInterface::class, WhatsappMessageResponseFormatter::class],
    [SearchTechnicalNotebookAdapterInterface::class, SearchTechnicalNotebookAdapter::class],
    [WhatsappMessageSearchAdapterInterface::class, WhatsappMessageSearchAdapter::class],
    [TechnicalNotebookSheetMapperInterface::class, TechnicalNotebookSheetMapper::class],
    [SearchTechnicalNotebookUsecaseInterface::class, SearchTechnicalNotebookUsecase::class],
    [WhatsappMessageInterpretationParserInterface::class, WhatsappMessageInterpretationParser::class],
    [InterpretWhatsappMessageWithAiServiceInterface::class, InterpretWhatsappMessageWithAiService::class],
    [MunicipalityExtractorServiceInterface::class, MunicipalityExtractorService::class],
    [SeiProcessWhatsappMessageInterpretationRuleInterface::class, SeiProcessWhatsappMessageInterpretationRule::class],
    [WhatsappMessageInterpretationRuleInterface::class, MunicipalityWhatsappMessageInterpretationRule::class],
    [DirectWhatsappMessageInterpreterServiceInterface::class, DirectWhatsappMessageInterpreterService::class],
    [ResolveWhatsappMessageInterpretationServiceInterface::class, ResolveWhatsappMessageInterpretationService::class],
    [AcceptedWhatsappMessageInterpretationServiceInterface::class, AcceptedWhatsappMessageInterpretationService::class],
    [TechnicalNotebookRepositoryInterface::class, TechnicalNotebookGoogleSheetGatewayRepository::class],
]);

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
