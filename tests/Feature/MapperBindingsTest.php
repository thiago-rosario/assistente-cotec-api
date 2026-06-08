<?php

use App\Core\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\Core\Application\Interfaces\Mapper\ConstructionDemandSheetMapperInterface;
use App\Core\Application\Interfaces\Mapper\LandSurveySheetMapperInterface;
use App\Core\Application\Interfaces\Mapper\NotebookSheetMapperInterface;
use App\Core\Application\Interfaces\Mapper\TechnicalNotebookSheetMapperInterface;
use App\Core\Application\Interfaces\Mapper\TravelItinerarySheetMapperInterface;
use App\Core\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\Core\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\Core\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\Core\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Rules\MunicipalityWhatsappMessageInterpretationRule;
use App\Core\Application\Rules\SeiProcessWhatsappMessageInterpretationRule;
use App\Core\Application\Service\DirectWhatsappMessageInterpreterService;
use App\Core\Application\Service\GreetingMessageMatcherService;
use App\Core\Application\Service\MunicipalityExtractorService;
use App\Core\Application\Service\ResolveWhatsappMessageInterpretationService;
use App\Core\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Domain\Repository\ConstructionDemandRepositoryInterface;
use App\Core\Domain\Repository\LandSurveyRepositoryInterface;
use App\Core\Domain\Repository\NotebookRepositoryInterface;
use App\Core\Domain\Repository\TechnicalNotebookRepositoryInterface;
use App\Core\Domain\Repository\TravelItineraryRepositoryInterface;
use App\Core\Infra\Adapter\WhatsappMessageSearchAdapter;
use App\Core\Infra\Mapper\ConstructionDemandSheetMapper;
use App\Core\Infra\Mapper\LandSurveySheetMapper;
use App\Core\Infra\Mapper\NotebookSheetMapper;
use App\Core\Infra\Mapper\TechnicalNotebookSheetMapper;
use App\Core\Infra\Mapper\TravelItinerarySheetMapper;
use App\Core\Infra\Repository\Gateway\ConstructionDemandGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\Gateway\LandSurveyGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\Gateway\NotebookGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\Gateway\TechnicalNotebookGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\Gateway\TravelItineraryGoogleSheetGatewayRepository;
use App\Core\Infra\Service\WhatsappMessageResponseFormatter;

it('resolves spreadsheet mapper interfaces from the container', function (
    string $abstract,
    string $concrete,
) {
    expect(app($abstract))->toBeInstanceOf($concrete);
})->with([
    [ConstructionDemandSheetMapperInterface::class, ConstructionDemandSheetMapper::class],
    [LandSurveySheetMapperInterface::class, LandSurveySheetMapper::class],
    [NotebookSheetMapperInterface::class, NotebookSheetMapper::class],
    [TechnicalNotebookSheetMapperInterface::class, TechnicalNotebookSheetMapper::class],
    [TravelItinerarySheetMapperInterface::class, TravelItinerarySheetMapper::class],
    [ConstructionDemandRepositoryInterface::class, ConstructionDemandGoogleSheetGatewayRepository::class],
    [LandSurveyRepositoryInterface::class, LandSurveyGoogleSheetGatewayRepository::class],
    [NotebookRepositoryInterface::class, NotebookGoogleSheetGatewayRepository::class],
    [TechnicalNotebookRepositoryInterface::class, TechnicalNotebookGoogleSheetGatewayRepository::class],
    [TravelItineraryRepositoryInterface::class, TravelItineraryGoogleSheetGatewayRepository::class],
    [GreetingMessageMatcherServiceInterface::class, GreetingMessageMatcherService::class],
    [MunicipalityExtractorServiceInterface::class, MunicipalityExtractorService::class],
    [SeiProcessWhatsappMessageInterpretationRuleInterface::class, SeiProcessWhatsappMessageInterpretationRule::class],
    [WhatsappMessageInterpretationRuleInterface::class, MunicipalityWhatsappMessageInterpretationRule::class],
    [DirectWhatsappMessageInterpreterServiceInterface::class, DirectWhatsappMessageInterpreterService::class],
    [ResolveWhatsappMessageInterpretationServiceInterface::class, ResolveWhatsappMessageInterpretationService::class],
    [ProcessWhatsappMessageUsecaseInterface::class, ProcessWhatsappMessageUsecase::class],
    [WhatsappMessageSearchAdapterInterface::class, WhatsappMessageSearchAdapter::class],
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
});
