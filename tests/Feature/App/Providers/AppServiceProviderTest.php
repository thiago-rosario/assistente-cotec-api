<?php

use App\Core\Application\Interfaces\Adapter\ReadGoogleSpreadsheetAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchGoogleSheetAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\Core\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\Core\Application\Interfaces\Adapter\WhatsappWebhookPayloadAdapterInterface;
use App\Core\Application\Interfaces\Mapper\PythonMessagePayloadMapperInterface;
use App\Core\Application\Interfaces\Mapper\TechnicalNotebookSheetMapperInterface;
use App\Core\Application\Interfaces\Mapper\WhatsappWebhookPayloadMapperInterface;
use App\Core\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\Core\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\Core\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\Core\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\Core\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageSenderInterface;
use App\Core\Application\Interfaces\Usecase\AcceptIncomingWhatsappWebhookUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\ProcessIncomingWhatsappWebhookUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Rules\MunicipalityWhatsappMessageInterpretationRule;
use App\Core\Application\Rules\SeiProcessWhatsappMessageInterpretationRule;
use App\Core\Application\Service\AcceptedWhatsappMessageInterpretationService;
use App\Core\Application\Service\DirectWhatsappMessageInterpreterService;
use App\Core\Application\Service\GreetingMessageMatcherService;
use App\Core\Application\Service\MunicipalityExtractorService;
use App\Core\Application\Service\ResolveWhatsappMessageInterpretationService;
use App\Core\Application\Usecase\AcceptIncomingWhatsappWebhookUsecase;
use App\Core\Application\Usecase\ProcessIncomingWhatsappWebhookUsecase;
use App\Core\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Domain\Repository\GoogleSheetRepositoryInterface;
use App\Core\Domain\Repository\TechnicalNotebookRepositoryInterface;
use App\Core\Infra\Adapter\ReadGoogleSpreadsheetAdapter;
use App\Core\Infra\Adapter\SearchGoogleSheetAdapter;
use App\Core\Infra\Adapter\SearchTechnicalNotebookAdapter;
use App\Core\Infra\Adapter\WhatsappMessageSearchAdapter;
use App\Core\Infra\Adapter\WhatsappWebhookPayloadAdapter;
use App\Core\Infra\External\EditaCodigoWhatsappMessageSender;
use App\Core\Infra\Mapper\PythonMessagePayloadMapper;
use App\Core\Infra\Mapper\TechnicalNotebookSheetMapper;
use App\Core\Infra\Mapper\WhatsappWebhookPayloadMapper;
use App\Core\Infra\Repository\Gateway\GoogleSheetGateway;
use App\Core\Infra\Repository\Gateway\TechnicalNotebookGoogleSheetGatewayRepository;
use App\Core\Infra\Service\WhatsappMessageResponseFormatter;

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
    [WhatsappWebhookPayloadAdapterInterface::class, WhatsappWebhookPayloadAdapter::class],
    [ProcessWhatsappMessageUsecaseInterface::class, ProcessWhatsappMessageUsecase::class],
    [AcceptIncomingWhatsappWebhookUsecaseInterface::class, AcceptIncomingWhatsappWebhookUsecase::class],
    [ProcessIncomingWhatsappWebhookUsecaseInterface::class, ProcessIncomingWhatsappWebhookUsecase::class],
    [TechnicalNotebookSheetMapperInterface::class, TechnicalNotebookSheetMapper::class],
    [WhatsappWebhookPayloadMapperInterface::class, WhatsappWebhookPayloadMapper::class],
    [PythonMessagePayloadMapperInterface::class, PythonMessagePayloadMapper::class],
    [GoogleSheetRepositoryInterface::class, GoogleSheetGateway::class],
    [TechnicalNotebookRepositoryInterface::class, TechnicalNotebookGoogleSheetGatewayRepository::class],
    [GreetingMessageMatcherServiceInterface::class, GreetingMessageMatcherService::class],
    [MunicipalityExtractorServiceInterface::class, MunicipalityExtractorService::class],
    [SeiProcessWhatsappMessageInterpretationRuleInterface::class, SeiProcessWhatsappMessageInterpretationRule::class],
    [WhatsappMessageInterpretationRuleInterface::class, MunicipalityWhatsappMessageInterpretationRule::class],
    [DirectWhatsappMessageInterpreterServiceInterface::class, DirectWhatsappMessageInterpreterService::class],
    [ResolveWhatsappMessageInterpretationServiceInterface::class, ResolveWhatsappMessageInterpretationService::class],
    [AcceptedWhatsappMessageInterpretationServiceInterface::class, AcceptedWhatsappMessageInterpretationService::class],
    [WhatsappMessageSenderInterface::class, EditaCodigoWhatsappMessageSender::class],
    [WhatsappMessageResponseFormatterInterface::class, WhatsappMessageResponseFormatter::class],
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
