<?php

namespace App\Providers;

use App\Core\Application\Interfaces\ConstructionDemandSheetMapperInterface;
use App\Core\Application\Interfaces\GoogleSheetRowMapperInterface;
use App\Core\Application\Interfaces\InterpretWhatsappMessageWithAiServiceInterface;
use App\Core\Application\Interfaces\LandSurveySheetMapperInterface;
use App\Core\Application\Interfaces\NotebookSheetMapperInterface;
use App\Core\Application\Interfaces\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Interfaces\PythonBridgeEventParserInterface;
use App\Core\Application\Interfaces\PythonMessageOutputParserInterface;
use App\Core\Application\Interfaces\PythonMessagePayloadAdapterInterface;
use App\Core\Application\Interfaces\PythonMessagePayloadMapperInterface;
use App\Core\Application\Interfaces\ReadGoogleSpreadsheetAdapterInterface;
use App\Core\Application\Interfaces\ReadGoogleSpreadsheetUsecaseInterface;
use App\Core\Application\Interfaces\SearchConstructionDemandAdapterInterface;
use App\Core\Application\Interfaces\SearchConstructionDemandUsecaseInterface;
use App\Core\Application\Interfaces\SearchGoogleSheetAdapterInterface;
use App\Core\Application\Interfaces\SearchGoogleSheetUsecaseInterface;
use App\Core\Application\Interfaces\SearchLandSurveyAdapterInterface;
use App\Core\Application\Interfaces\SearchLandSurveyUsecaseInterface;
use App\Core\Application\Interfaces\SearchTechnicalNotebookAdapterInterface;
use App\Core\Application\Interfaces\SearchTechnicalNotebookUsecaseInterface;
use App\Core\Application\Interfaces\SearchTravelItineraryAdapterInterface;
use App\Core\Application\Interfaces\SearchTravelItineraryUsecaseInterface;
use App\Core\Application\Interfaces\TechnicalNotebookSheetMapperInterface;
use App\Core\Application\Interfaces\TravelItinerarySheetMapperInterface;
use App\Core\Application\Interfaces\WhatsappMessageInterpretationParserInterface;
use App\Core\Application\Interfaces\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\WhatsappMessageSearchAdapterInterface;
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
        $this->app->bind(WhatsappMessageSearchAdapterInterface::class, WhatsappMessageSearchAdapter::class);
        $this->app->bind(WhatsappMessageResponseFormatterInterface::class, WhatsappMessageResponseFormatter::class);
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
