<?php

declare(strict_types=1);

namespace App\Core\Infra\Provider;

use App\Core\Application\Interfaces\Adapter\ReadGoogleSpreadsheetAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchGoogleSheetAdapterInterface;
use App\Core\Application\Interfaces\Adapter\WhatsappWebhookPayloadAdapterInterface;
use App\Core\Application\Interfaces\Mapper\GoogleSheetRowMapperInterface;
use App\Core\Application\Interfaces\Mapper\WhatsappWebhookPayloadMapperInterface;
use App\Core\Application\Interfaces\Repository\WhatsappConversationStateStoreInterface;
use App\Core\Application\Interfaces\Service\CoreWhatsappResponseFormatterInterface;
use App\Core\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
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
use App\Core\Infra\Repository\WhatsappConversationStateStore;
use App\Core\Infra\Service\WhatsappCoreResponseFormatter;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReadGoogleSpreadsheetAdapterInterface::class, ReadGoogleSpreadsheetAdapter::class);
        $this->app->bind(ReadGoogleSpreadsheetUsecaseInterface::class, ReadGoogleSpreadsheetUsecase::class);
        $this->app->bind(SearchGoogleSheetAdapterInterface::class, SearchGoogleSheetAdapter::class);
        $this->app->bind(SearchGoogleSheetUsecaseInterface::class, SearchGoogleSheetUsecase::class);
        $this->app->bind(ProcessWhatsappMessageUsecaseInterface::class, ProcessWhatsappMessageUsecase::class);
        $this->app->bind(AcceptIncomingWhatsappWebhookUsecaseInterface::class, AcceptIncomingWhatsappWebhookUsecase::class);
        $this->app->bind(ProcessIncomingWhatsappWebhookUsecaseInterface::class, ProcessIncomingWhatsappWebhookUsecase::class);
        $this->app->bind(CoreWhatsappResponseFormatterInterface::class, WhatsappCoreResponseFormatter::class);
        $this->app->bind(GreetingMessageMatcherServiceInterface::class, GreetingMessageMatcherService::class);
        $this->app->bind(WhatsappConversationStateStoreInterface::class, WhatsappConversationStateStore::class);
        $this->app->bind(WhatsappMessageSenderInterface::class, EditaCodigoWhatsappMessageSender::class);
        $this->app->bind(GoogleSheetRowMapperInterface::class, GoogleSheetRowMapper::class);
        $this->app->bind(GoogleSheetRepositoryInterface::class, GoogleSheetGateway::class);
        $this->app->bind(WhatsappWebhookPayloadAdapterInterface::class, WhatsappWebhookPayloadAdapter::class);
        $this->app->bind(WhatsappWebhookPayloadMapperInterface::class, WhatsappWebhookPayloadMapper::class);
    }
}
