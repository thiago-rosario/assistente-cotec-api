<?php

namespace App\BuildPanel\Infra\Providers;

use App\BuildPanel\Application\Interfaces\Adapter\ReadGoogleSpreadsheetAdapterInterface;
use App\BuildPanel\Application\Interfaces\Adapter\SearchGoogleSheetAdapterInterface;
use App\BuildPanel\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\BuildPanel\Application\Interfaces\Mapper\GoogleSheetRowMapperInterface;
use App\BuildPanel\Application\Interfaces\Mapper\TechnicalNotebookSheetMapperInterface;
use App\BuildPanel\Application\Interfaces\Usecase\ReadGoogleSpreadsheetUsecaseInterface;
use App\BuildPanel\Application\Interfaces\Usecase\SearchGoogleSheetUsecaseInterface;
use App\BuildPanel\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;
use App\BuildPanel\Application\Usecase\ReadGoogleSpreadsheetUsecase;
use App\BuildPanel\Application\Usecase\SearchGoogleSheetUsecase;
use App\BuildPanel\Application\Usecase\SearchTechnicalNotebookUsecase;
use App\BuildPanel\Domain\Repository\GoogleSheetRepositoryInterface;
use App\BuildPanel\Domain\Repository\TechnicalNotebookRepositoryInterface;
use App\BuildPanel\Infra\Adapter\ReadGoogleSpreadsheetAdapter;
use App\BuildPanel\Infra\Adapter\SearchGoogleSheetAdapter;
use App\BuildPanel\Infra\Adapter\SearchTechnicalNotebookAdapter;
use App\BuildPanel\Infra\Adapter\TechnicalNotebookWhatsappSearchHandler;
use App\BuildPanel\Infra\Mapper\GoogleSheetRowMapper;
use App\BuildPanel\Infra\Mapper\TechnicalNotebookSheetMapper;
use App\BuildPanel\Infra\Message\TechnicalNotebookFoundRecordsReplyBuilder;
use App\BuildPanel\Infra\Message\WhatsappDefaultReplies;
use App\BuildPanel\Infra\Repository\Gateway\GoogleSheetGateway;
use App\BuildPanel\Infra\Repository\Gateway\TechnicalNotebookGoogleSheetGatewayRepository;
use App\Core\Application\Interfaces\Service\WhatsappDefaultRepliesInterface;
use Illuminate\Support\ServiceProvider;

class BuildPanelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReadGoogleSpreadsheetAdapterInterface::class, ReadGoogleSpreadsheetAdapter::class);
        $this->app->bind(ReadGoogleSpreadsheetUsecaseInterface::class, ReadGoogleSpreadsheetUsecase::class);
        $this->app->bind(SearchGoogleSheetAdapterInterface::class, SearchGoogleSheetAdapter::class);
        $this->app->bind(SearchTechnicalNotebookAdapterInterface::class, SearchTechnicalNotebookAdapter::class);
        $this->app->bind(SearchGoogleSheetUsecaseInterface::class, SearchGoogleSheetUsecase::class);
        $this->app->bind(SearchTechnicalNotebookUsecaseInterface::class, SearchTechnicalNotebookUsecase::class);
        $this->app->bind(GoogleSheetRowMapperInterface::class, GoogleSheetRowMapper::class);
        $this->app->bind(TechnicalNotebookSheetMapperInterface::class, TechnicalNotebookSheetMapper::class);
        $this->app->bind(GoogleSheetRepositoryInterface::class, GoogleSheetGateway::class);
        $this->app->bind(TechnicalNotebookRepositoryInterface::class, TechnicalNotebookGoogleSheetGatewayRepository::class);
        $this->app->bind(WhatsappDefaultRepliesInterface::class, WhatsappDefaultReplies::class);
        $this->app->tag([TechnicalNotebookWhatsappSearchHandler::class], 'whatsapp.search_handlers');
        $this->app->tag([TechnicalNotebookFoundRecordsReplyBuilder::class], 'whatsapp.found_records_reply_builders');
    }

    public function boot(): void {}
}
