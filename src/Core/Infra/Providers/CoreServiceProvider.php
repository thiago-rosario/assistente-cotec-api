<?php

namespace App\Core\Infra\Providers;

use App\Core\Application\Interfaces\Adapter\PythonMessagePayloadAdapterInterface;
use App\Core\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\Core\Application\Interfaces\Mapper\PythonMessagePayloadMapperInterface;
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
use App\Core\Application\Interfaces\Service\WhatsappDefaultRepliesInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Rules\MunicipalityWhatsappMessageInterpretationRule;
use App\Core\Application\Rules\SeiProcessWhatsappMessageInterpretationRule;
use App\Core\Application\Service\AcceptedWhatsappMessageInterpretationService;
use App\Core\Application\Service\DirectWhatsappMessageInterpreterService;
use App\Core\Application\Service\GreetingMessageMatcherService;
use App\Core\Application\Service\MunicipalityExtractorService;
use App\Core\Application\Service\ResolveWhatsappMessageInterpretationService;
use App\Core\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Infra\Adapter\PythonMessagePayloadAdapter;
use App\Core\Infra\Adapter\WhatsappMessageSearchAdapter;
use App\Core\Infra\Mapper\PythonMessagePayloadMapper;
use App\Core\Infra\Message\WhatsappResponsePayloadFactory;
use App\Core\Infra\Parser\PythonBridgeEventParser;
use App\Core\Infra\Parser\PythonMessageOutputParser;
use App\Core\Infra\Parser\WhatsappMessageInterpretationParser;
use App\Core\Infra\Service\InterpretWhatsappMessageWithAiService;
use App\Core\Infra\Service\WhatsappMessageResponseFormatter;
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
