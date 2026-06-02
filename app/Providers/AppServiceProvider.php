<?php

namespace App\Providers;

use App\Core\Application\Interfaces\GoogleSheetRowMapperInterface;
use App\Core\Application\Interfaces\PythonBridgeEventParserInterface;
use App\Core\Application\Interfaces\PythonMessageOutputParserInterface;
use App\Core\Application\Interfaces\PythonMessagePayloadAdapterInterface;
use App\Core\Application\Interfaces\PythonMessagePayloadMapperInterface;
use App\Core\Application\Interfaces\ReadGoogleSpreadsheetAdapterInterface;
use App\Core\Application\Interfaces\ReadGoogleSpreadsheetUsecaseInterface;
use App\Core\Application\Interfaces\SearchConstructionDemandUsecaseInterface;
use App\Core\Application\Interfaces\SearchGoogleSheetAdapterInterface;
use App\Core\Application\Interfaces\SearchGoogleSheetUsecaseInterface;
use App\Core\Application\Interfaces\SearchLandSurveyUsecaseInterface;
use App\Core\Application\Interfaces\SearchTravelItineraryUsecaseInterface;
use App\Core\Application\Usecase\ReadGoogleSpreadsheetUsecase;
use App\Core\Application\Usecase\SearchConstructionDemandUsecase;
use App\Core\Application\Usecase\SearchGoogleSheetUsecase;
use App\Core\Application\Usecase\SearchLandSurveyUsecase;
use App\Core\Application\Usecase\SearchTravelItineraryUsecase;
use App\Core\Domain\Repository\GoogleSheetRepositoryInterface;
use App\Core\Infra\Adapter\PythonMessagePayloadAdapter;
use App\Core\Infra\Adapter\ReadGoogleSpreadsheetAdapter;
use App\Core\Infra\Adapter\SearchGoogleSheetAdapter;
use App\Core\Infra\Mapper\GoogleSheetRowMapper;
use App\Core\Infra\Mapper\PythonMessagePayloadMapper;
use App\Core\Infra\Parser\PythonBridgeEventParser;
use App\Core\Infra\Parser\PythonMessageOutputParser;
use App\Core\Infra\Repository\Gateway\GoogleSheetGateway;
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
        $this->app->bind(SearchGoogleSheetUsecaseInterface::class, SearchGoogleSheetUsecase::class);
        $this->app->bind(SearchConstructionDemandUsecaseInterface::class, SearchConstructionDemandUsecase::class);
        $this->app->bind(SearchLandSurveyUsecaseInterface::class, SearchLandSurveyUsecase::class);
        $this->app->bind(SearchTravelItineraryUsecaseInterface::class, SearchTravelItineraryUsecase::class);
        $this->app->bind(GoogleSheetRowMapperInterface::class, GoogleSheetRowMapper::class);
        $this->app->bind(GoogleSheetRepositoryInterface::class, GoogleSheetGateway::class);
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
