<?php

declare(strict_types=1);

namespace App\BuildPanel\Infra\Providers;

use App\BuildPanel\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\BuildPanel\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\BuildPanel\Application\Interfaces\Mapper\TechnicalNotebookSheetMapperInterface;
use App\BuildPanel\Application\Interfaces\Parser\WhatsappMessageInterpretationParserInterface;
use App\BuildPanel\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\BuildPanel\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\BuildPanel\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\BuildPanelWhatsappMessageServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\InterpretWhatsappMessageWithAiServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\BuildPanel\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;
use App\BuildPanel\Application\Rules\MunicipalityWhatsappMessageInterpretationRule;
use App\BuildPanel\Application\Rules\SeiProcessWhatsappMessageInterpretationRule;
use App\BuildPanel\Application\Service\AcceptedWhatsappMessageInterpretationService;
use App\BuildPanel\Application\Service\BuildPanelWhatsappMessageService;
use App\BuildPanel\Application\Service\DirectWhatsappMessageInterpreterService;
use App\BuildPanel\Application\Service\MunicipalityExtractorService;
use App\BuildPanel\Application\Service\ResolveWhatsappMessageInterpretationService;
use App\BuildPanel\Application\Usecase\SearchTechnicalNotebookUsecase;
use App\BuildPanel\Domain\Repository\TechnicalNotebookRepositoryInterface;
use App\BuildPanel\Infra\Adapter\SearchTechnicalNotebookAdapter;
use App\BuildPanel\Infra\Adapter\WhatsappMessageSearchAdapter;
use App\BuildPanel\Infra\Mapper\TechnicalNotebookSheetMapper;
use App\BuildPanel\Infra\Parser\WhatsappMessageInterpretationParser;
use App\BuildPanel\Infra\Repository\Gateway\TechnicalNotebookGoogleSheetGatewayRepository;
use App\BuildPanel\Infra\Service\InterpretWhatsappMessageWithAiService;
use App\BuildPanel\Infra\Service\WhatsappMessageResponseFormatter;
use Illuminate\Support\ServiceProvider;

class BuildPanelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SearchTechnicalNotebookAdapterInterface::class, SearchTechnicalNotebookAdapter::class);
        $this->app->bind(WhatsappMessageSearchAdapterInterface::class, WhatsappMessageSearchAdapter::class);
        $this->app->bind(SearchTechnicalNotebookUsecaseInterface::class, SearchTechnicalNotebookUsecase::class);
        $this->app->bind(InterpretWhatsappMessageWithAiServiceInterface::class, InterpretWhatsappMessageWithAiService::class);
        $this->app->bind(WhatsappMessageInterpretationParserInterface::class, WhatsappMessageInterpretationParser::class);
        $this->app->bind(MunicipalityExtractorServiceInterface::class, MunicipalityExtractorService::class);
        $this->app->bind(SeiProcessWhatsappMessageInterpretationRuleInterface::class, SeiProcessWhatsappMessageInterpretationRule::class);
        $this->app->bind(WhatsappMessageInterpretationRuleInterface::class, MunicipalityWhatsappMessageInterpretationRule::class);
        $this->app->bind(DirectWhatsappMessageInterpreterServiceInterface::class, DirectWhatsappMessageInterpreterService::class);
        $this->app->bind(ResolveWhatsappMessageInterpretationServiceInterface::class, ResolveWhatsappMessageInterpretationService::class);
        $this->app->bind(WhatsappMessageResponseFormatterInterface::class, WhatsappMessageResponseFormatter::class);
        $this->app->bind(AcceptedWhatsappMessageInterpretationServiceInterface::class, AcceptedWhatsappMessageInterpretationService::class);
        $this->app->bind(BuildPanelWhatsappMessageServiceInterface::class, BuildPanelWhatsappMessageService::class);
        $this->app->bind(TechnicalNotebookSheetMapperInterface::class, TechnicalNotebookSheetMapper::class);
        $this->app->bind(TechnicalNotebookRepositoryInterface::class, TechnicalNotebookGoogleSheetGatewayRepository::class);
    }
}
