<?php

namespace App\Providers;

use App\Core\Application\Interfaces\Adapter\PythonMessagePayloadAdapterInterface;
use App\Core\Application\Interfaces\Adapter\ReadGoogleSpreadsheetAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchConstructionDemandAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchGoogleSheetAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchLandSurveyAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchTravelItineraryAdapterInterface;
use App\Core\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\Core\Application\Interfaces\Mapper\ConstructionDemandSheetMapperInterface;
use App\Core\Application\Interfaces\Mapper\GoogleSheetRowMapperInterface;
use App\Core\Application\Interfaces\Mapper\LandSurveySheetMapperInterface;
use App\Core\Application\Interfaces\Mapper\NotebookSheetMapperInterface;
use App\Core\Application\Interfaces\Mapper\PythonMessagePayloadMapperInterface;
use App\Core\Application\Interfaces\Mapper\TechnicalNotebookSheetMapperInterface;
use App\Core\Application\Interfaces\Mapper\TravelItinerarySheetMapperInterface;
use App\Core\Application\Interfaces\Parser\PythonBridgeEventParserInterface;
use App\Core\Application\Interfaces\Parser\PythonMessageOutputParserInterface;
use App\Core\Application\Interfaces\Parser\WhatsappMessageInterpretationParserInterface;
use App\Core\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\Core\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\Core\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\Core\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Application\Interfaces\Service\InterpretWhatsappMessageWithAiServiceInterface;
use App\Core\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\Core\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\ReadGoogleSpreadsheetUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\SearchConstructionDemandUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\SearchGoogleSheetUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\SearchLandSurveyUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\SearchTravelItineraryUsecaseInterface;
use App\Core\Application\Rules\MunicipalityWhatsappMessageInterpretationRule;
use App\Core\Application\Rules\SeiProcessWhatsappMessageInterpretationRule;
use App\Core\Application\Service\AcceptedWhatsappMessageInterpretationService;
use App\Core\Application\Service\DirectWhatsappMessageInterpreterService;
use App\Core\Application\Service\GreetingMessageMatcherService;
use App\Core\Application\Service\MunicipalityExtractorService;
use App\Core\Application\Service\ResolveWhatsappMessageInterpretationService;
use App\Core\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Application\Usecase\ReadGoogleSpreadsheetUsecase;
use App\Core\Application\Usecase\SearchConstructionDemandUsecase;
use App\Core\Application\Usecase\SearchGoogleSheetUsecase;
use App\Core\Application\Usecase\SearchLandSurveyUsecase;
use App\Core\Application\Usecase\SearchTechnicalNotebookUsecase;
use App\Core\Application\Usecase\SearchTravelItineraryUsecase;
use App\Core\Domain\Repository\ConstructionDemandRepositoryInterface;
use App\Core\Domain\Repository\GoogleSheetRepositoryInterface;
use App\Core\Domain\Repository\LandSurveyRepositoryInterface;
use App\Core\Domain\Repository\NotebookRepositoryInterface;
use App\Core\Domain\Repository\TechnicalNotebookRepositoryInterface;
use App\Core\Domain\Repository\TravelItineraryRepositoryInterface;
use App\Core\Infra\Adapter\PythonMessagePayloadAdapter;
use App\Core\Infra\Adapter\ReadGoogleSpreadsheetAdapter;
use App\Core\Infra\Adapter\SearchConstructionDemandAdapter;
use App\Core\Infra\Adapter\SearchGoogleSheetAdapter;
use App\Core\Infra\Adapter\SearchLandSurveyAdapter;
use App\Core\Infra\Adapter\SearchTechnicalNotebookAdapter;
use App\Core\Infra\Adapter\SearchTravelItineraryAdapter;
use App\Core\Infra\Adapter\WhatsappMessageSearchAdapter;
use App\Core\Infra\Mapper\ConstructionDemandSheetMapper;
use App\Core\Infra\Mapper\GoogleSheetRowMapper;
use App\Core\Infra\Mapper\LandSurveySheetMapper;
use App\Core\Infra\Mapper\NotebookSheetMapper;
use App\Core\Infra\Mapper\PythonMessagePayloadMapper;
use App\Core\Infra\Mapper\TechnicalNotebookSheetMapper;
use App\Core\Infra\Mapper\TravelItinerarySheetMapper;
use App\Core\Infra\Parser\PythonBridgeEventParser;
use App\Core\Infra\Parser\PythonMessageOutputParser;
use App\Core\Infra\Parser\WhatsappMessageInterpretationParser;
use App\Core\Infra\Repository\Gateway\ConstructionDemandGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\Gateway\GoogleSheetGateway;
use App\Core\Infra\Repository\Gateway\LandSurveyGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\Gateway\NotebookGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\Gateway\TechnicalNotebookGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\Gateway\TravelItineraryGoogleSheetGatewayRepository;
use App\Core\Infra\Service\InterpretWhatsappMessageWithAiService;
use App\Core\Infra\Service\WhatsappMessageResponseFormatter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ReadGoogleSpreadsheetAdapterInterface::class, ReadGoogleSpreadsheetAdapter::class);
        $this->app->bind(ReadGoogleSpreadsheetUsecaseInterface::class, ReadGoogleSpreadsheetUsecase::class);
        $this->app->bind(SearchGoogleSheetAdapterInterface::class, SearchGoogleSheetAdapter::class);
        $this->app->bind(SearchConstructionDemandAdapterInterface::class, SearchConstructionDemandAdapter::class);
        $this->app->bind(SearchLandSurveyAdapterInterface::class, SearchLandSurveyAdapter::class);
        $this->app->bind(SearchTechnicalNotebookAdapterInterface::class, SearchTechnicalNotebookAdapter::class);
        $this->app->bind(SearchTravelItineraryAdapterInterface::class, SearchTravelItineraryAdapter::class);
        $this->app->bind(SearchGoogleSheetUsecaseInterface::class, SearchGoogleSheetUsecase::class);
        $this->app->bind(SearchConstructionDemandUsecaseInterface::class, SearchConstructionDemandUsecase::class);
        $this->app->bind(SearchLandSurveyUsecaseInterface::class, SearchLandSurveyUsecase::class);
        $this->app->bind(SearchTechnicalNotebookUsecaseInterface::class, SearchTechnicalNotebookUsecase::class);
        $this->app->bind(SearchTravelItineraryUsecaseInterface::class, SearchTravelItineraryUsecase::class);
        $this->app->bind(ProcessWhatsappMessageUsecaseInterface::class, ProcessWhatsappMessageUsecase::class);
        $this->app->bind(InterpretWhatsappMessageWithAiServiceInterface::class, InterpretWhatsappMessageWithAiService::class);
        $this->app->bind(WhatsappMessageInterpretationParserInterface::class, WhatsappMessageInterpretationParser::class);
        $this->app->bind(GreetingMessageMatcherServiceInterface::class, GreetingMessageMatcherService::class);
        $this->app->bind(MunicipalityExtractorServiceInterface::class, MunicipalityExtractorService::class);
        $this->app->bind(SeiProcessWhatsappMessageInterpretationRuleInterface::class, SeiProcessWhatsappMessageInterpretationRule::class);
        $this->app->bind(WhatsappMessageInterpretationRuleInterface::class, MunicipalityWhatsappMessageInterpretationRule::class);
        $this->app->bind(DirectWhatsappMessageInterpreterServiceInterface::class, DirectWhatsappMessageInterpreterService::class);
        $this->app->bind(ResolveWhatsappMessageInterpretationServiceInterface::class, ResolveWhatsappMessageInterpretationService::class);
        $this->app->bind(WhatsappMessageSearchAdapterInterface::class, WhatsappMessageSearchAdapter::class);
        $this->app->bind(WhatsappMessageResponseFormatterInterface::class, WhatsappMessageResponseFormatter::class);
        $this->app->bind(AcceptedWhatsappMessageInterpretationServiceInterface::class, AcceptedWhatsappMessageInterpretationService::class);
        $this->app->bind(GoogleSheetRowMapperInterface::class, GoogleSheetRowMapper::class);
        $this->app->bind(ConstructionDemandSheetMapperInterface::class, ConstructionDemandSheetMapper::class);
        $this->app->bind(LandSurveySheetMapperInterface::class, LandSurveySheetMapper::class);
        $this->app->bind(NotebookSheetMapperInterface::class, NotebookSheetMapper::class);
        $this->app->bind(TechnicalNotebookSheetMapperInterface::class, TechnicalNotebookSheetMapper::class);
        $this->app->bind(TravelItinerarySheetMapperInterface::class, TravelItinerarySheetMapper::class);
        $this->app->bind(GoogleSheetRepositoryInterface::class, GoogleSheetGateway::class);
        $this->app->bind(ConstructionDemandRepositoryInterface::class, ConstructionDemandGoogleSheetGatewayRepository::class);
        $this->app->bind(LandSurveyRepositoryInterface::class, LandSurveyGoogleSheetGatewayRepository::class);
        $this->app->bind(NotebookRepositoryInterface::class, NotebookGoogleSheetGatewayRepository::class);
        $this->app->bind(TechnicalNotebookRepositoryInterface::class, TechnicalNotebookGoogleSheetGatewayRepository::class);
        $this->app->bind(TravelItineraryRepositoryInterface::class, TravelItineraryGoogleSheetGatewayRepository::class);
        $this->app->bind(PythonMessagePayloadAdapterInterface::class, PythonMessagePayloadAdapter::class);
        $this->app->bind(PythonBridgeEventParserInterface::class, PythonBridgeEventParser::class);
        $this->app->bind(PythonMessagePayloadMapperInterface::class, PythonMessagePayloadMapper::class);
        $this->app->bind(PythonMessageOutputParserInterface::class, PythonMessageOutputParser::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
