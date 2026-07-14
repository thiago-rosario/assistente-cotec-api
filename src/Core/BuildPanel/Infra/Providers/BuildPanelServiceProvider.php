<?php

namespace App\Core\BuildPanel\Infra\Providers;

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
use App\Core\BuildPanel\Infra\Repository\Gateway\GoogleSheetGateway;
use App\Core\BuildPanel\Infra\Repository\Gateway\TechnicalNotebookGoogleSheetGatewayRepository;
use App\Core\Conversation\Application\Interfaces\Service\WhatsappDefaultRepliesInterface;
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
