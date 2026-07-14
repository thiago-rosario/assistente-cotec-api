<?php

namespace App\Core\Conversation\Infra\Providers;

use App\Core\Conversation\Application\Interfaces\Adapter\PythonMessagePayloadAdapterInterface;
use App\Core\Conversation\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\Core\Conversation\Application\Interfaces\Mapper\PythonMessagePayloadMapperInterface;
use App\Core\Conversation\Application\Interfaces\Parser\PythonBridgeEventParserInterface;
use App\Core\Conversation\Application\Interfaces\Parser\PythonMessageOutputParserInterface;
use App\Core\Conversation\Application\Interfaces\Parser\WhatsappMessageInterpretationParserInterface;
use App\Core\Conversation\Application\Interfaces\Repository\ConversationStateRepositoryInterface;
use App\Core\Conversation\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\Core\Conversation\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\Core\Conversation\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\InterpretWhatsappMessageWithAiServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\WhatsappDefaultRepliesInterface;
use App\Core\Conversation\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Conversation\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Conversation\Application\Rules\MunicipalityWhatsappMessageInterpretationRule;
use App\Core\Conversation\Application\Rules\SeiProcessWhatsappMessageInterpretationRule;
use App\Core\Conversation\Application\Service\AcceptedWhatsappMessageInterpretationService;
use App\Core\Conversation\Application\Service\DirectWhatsappMessageInterpreterService;
use App\Core\Conversation\Application\Service\GreetingMessageMatcherService;
use App\Core\Conversation\Application\Service\MunicipalityExtractorService;
use App\Core\Conversation\Application\Service\ResolveWhatsappMessageInterpretationService;
use App\Core\Conversation\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Conversation\Infra\Adapter\PythonMessagePayloadAdapter;
use App\Core\Conversation\Infra\Adapter\WhatsappMessageSearchAdapter;
use App\Core\Conversation\Infra\Mapper\PythonMessagePayloadMapper;
use App\Core\Conversation\Infra\Message\WhatsappResponsePayloadFactory;
use App\Core\Conversation\Infra\Parser\PythonBridgeEventParser;
use App\Core\Conversation\Infra\Parser\PythonMessageOutputParser;
use App\Core\Conversation\Infra\Parser\WhatsappMessageInterpretationParser;
use App\Core\Conversation\Infra\Repository\CacheConversationStateRepository;
use App\Core\Conversation\Infra\Service\InterpretWhatsappMessageWithAiService;
use App\Core\Conversation\Infra\Service\WhatsappMessageResponseFormatter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProcessWhatsappMessageUsecaseInterface::class, ProcessWhatsappMessageUsecase::class);
        $this->app->bind(InterpretWhatsappMessageWithAiServiceInterface::class, InterpretWhatsappMessageWithAiService::class);
        $this->app->bind(WhatsappMessageInterpretationParserInterface::class, WhatsappMessageInterpretationParser::class);
        $this->app->bind(GreetingMessageMatcherServiceInterface::class, GreetingMessageMatcherService::class);
        $this->app->bind(MunicipalityExtractorServiceInterface::class, MunicipalityExtractorService::class);
        $this->app->bind(SeiProcessWhatsappMessageInterpretationRuleInterface::class, SeiProcessWhatsappMessageInterpretationRule::class);
        $this->app->bind(WhatsappMessageInterpretationRuleInterface::class, MunicipalityWhatsappMessageInterpretationRule::class);
        $this->app->bind(DirectWhatsappMessageInterpreterServiceInterface::class, DirectWhatsappMessageInterpreterService::class);
        $this->app->bind(ResolveWhatsappMessageInterpretationServiceInterface::class, ResolveWhatsappMessageInterpretationService::class);
        $this->app->bind(AcceptedWhatsappMessageInterpretationServiceInterface::class, AcceptedWhatsappMessageInterpretationService::class);
        $this->app->bind(PythonMessagePayloadAdapterInterface::class, PythonMessagePayloadAdapter::class);
        $this->app->bind(PythonBridgeEventParserInterface::class, PythonBridgeEventParser::class);
        $this->app->bind(PythonMessagePayloadMapperInterface::class, PythonMessagePayloadMapper::class);
        $this->app->bind(PythonMessageOutputParserInterface::class, PythonMessageOutputParser::class);
        $this->app->bind(ConversationStateRepositoryInterface::class, CacheConversationStateRepository::class);

        $this->app->bind(
            WhatsappMessageSearchAdapterInterface::class,
            fn (Application $app): WhatsappMessageSearchAdapter => new WhatsappMessageSearchAdapter(
                handlers: $app->tagged('whatsapp.search_handlers'),
            ),
        );

        $this->app->bind(
            WhatsappMessageResponseFormatterInterface::class,
            fn (Application $app): WhatsappMessageResponseFormatter => new WhatsappMessageResponseFormatter(
                defaultReplies: $app->make(WhatsappDefaultRepliesInterface::class),
                payloadFactory: $app->make(WhatsappResponsePayloadFactory::class),
                foundRecordsReplyBuilders: $app->tagged('whatsapp.found_records_reply_builders'),
            ),
        );
    }

    public function boot(): void {}
}
