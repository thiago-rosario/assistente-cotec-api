<?php

declare(strict_types=1);

namespace App\Core\Infra\Provider;

use App\Core\Application\Factory\MessageFactory;
use App\Core\Application\Handler\BuildPanelFallbackWhatsappConversationFlowHandler;
use App\Core\Application\Handler\BuildPanelStateWhatsappConversationFlowHandler;
use App\Core\Application\Handler\MainMenuOptionWhatsappConversationFlowHandler;
use App\Core\Application\Handler\MainMenuRequestWhatsappConversationFlowHandler;
use App\Core\Application\Handler\UnsupportedWhatsappMessageContentHandler;
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
use App\Core\Infra\External\GoogleAuthenticationService;
use App\Core\Infra\External\LogWhatsappMessageSender;
use App\Core\Infra\Mapper\GoogleSheetRowMapper;
use App\Core\Infra\Mapper\WhatsappWebhookPayloadMapper;
use App\Core\Infra\Message\WhatsappMainMenuMessageBuilder;
use App\Core\Infra\Repository\EloquentRepository\CacheWhatsappConversationStateRepository;
use App\Core\Infra\Repository\Gateway\GoogleSheetGateway;
use App\TechnicalInspectionReport\Application\Handler\TechnicalInspectionReportWhatsappConversationFlowHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GoogleAuthenticationService::class);
        $this->app->bind(ReadGoogleSpreadsheetAdapterInterface::class, ReadGoogleSpreadsheetAdapter::class);
        $this->app->bind(ReadGoogleSpreadsheetUsecaseInterface::class, ReadGoogleSpreadsheetUsecase::class);
        $this->app->bind(SearchGoogleSheetAdapterInterface::class, SearchGoogleSheetAdapter::class);
        $this->app->bind(SearchGoogleSheetUsecaseInterface::class, SearchGoogleSheetUsecase::class);
        $this->app->bind(ProcessWhatsappMessageUsecaseInterface::class, ProcessWhatsappMessageUsecase::class);
        $this->app->bind(AcceptIncomingWhatsappWebhookUsecaseInterface::class, AcceptIncomingWhatsappWebhookUsecase::class);
        $this->app->bind(ProcessIncomingWhatsappWebhookUsecaseInterface::class, ProcessIncomingWhatsappWebhookUsecase::class);
        $this->app->bind(GreetingMessageMatcherServiceInterface::class, GreetingMessageMatcherService::class);
        $this->app->bind(MessageFactoryInterface::class, MessageFactory::class);
        $this->app->bind(MessageIntentResolverInterface::class, MessageIntentResolver::class);
        $this->app->bind(WhatsappMessageProcessorInterface::class, WhatsappMessageProcessorService::class);
        $this->app->bind(WhatsappBuildPanelFlowServiceInterface::class, WhatsappBuildPanelFlowService::class);
        $this->app->bind(WhatsappMainMenuServiceInterface::class, WhatsappMainMenuService::class);
        $this->app->bind(WhatsappMainMenuMessageBuilderInterface::class, WhatsappMainMenuMessageBuilder::class);
        $this->app->bind(LogWhatsappMessageSenderInterface::class, LogWhatsappMessageSender::class);
        $this->app->bind(
            WhatsappMessageSenderInterface::class,
            fn (Application $app): WhatsappMessageSenderInterface => match ($this->whatsappMessageSender()) {
                'log' => $app->make(LogWhatsappMessageSenderInterface::class),
                default => $app->make(EditaCodigoWhatsappMessageSender::class),
            },
        );
        $this->app->bind(GoogleSheetRowMapperInterface::class, GoogleSheetRowMapper::class);
        $this->app->bind(GoogleSheetRepositoryInterface::class, GoogleSheetGateway::class);
        $this->app->bind(WhatsappConversationStateRepositoryInterface::class, CacheWhatsappConversationStateRepository::class);
        $this->app->bind(WhatsappWebhookPayloadAdapterInterface::class, WhatsappWebhookPayloadAdapter::class);
        $this->app->bind(WhatsappWebhookPayloadMapperInterface::class, WhatsappWebhookPayloadMapper::class);
        $this->app->bind(
            WhatsappConversationFlowServiceInterface::class,
            fn (Application $app): WhatsappConversationFlowService => new WhatsappConversationFlowService([
                $app->make(UnsupportedWhatsappMessageContentHandler::class),
                $app->make(BuildPanelStateWhatsappConversationFlowHandler::class),
                $app->make(TechnicalInspectionReportWhatsappConversationFlowHandler::class),
                $app->make(MainMenuOptionWhatsappConversationFlowHandler::class),
                $app->make(MainMenuRequestWhatsappConversationFlowHandler::class),
                $app->make(BuildPanelFallbackWhatsappConversationFlowHandler::class),
            ]),
        );
    }

    private function whatsappMessageSender(): string
    {
        return strtolower(trim((string) (
            config('services.whatsapp.sender')
            ?? config('whatsapp.message_sender', 'editacodigo')
        )));
    }
}
