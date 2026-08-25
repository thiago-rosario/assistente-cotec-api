<?php

declare(strict_types=1);

namespace App\Contract\Infra\Providers;

use App\Contract\Application\Assembly\ContractSummaryAssembler;
use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Application\Interfaces\Assembly\ContractSummaryAssemblerInterface;
use App\Contract\Application\Interfaces\Mapper\ContractExecutionDeadlineSheetMapperInterface;
use App\Contract\Application\Interfaces\Mapper\ContractReadjustmentSheetMapperInterface;
use App\Contract\Application\Interfaces\Mapper\ContractSheetMapperInterface;
use App\Contract\Application\Interfaces\Mapper\ValueAdditiveSheetMapperInterface;
use App\Contract\Application\Interfaces\Parser\ContractDateParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractIntegerParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractMoneyParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNullableStringParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNumberParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractRequiredStringParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractSearchValueParserInterface;
use App\Contract\Application\Interfaces\Resolver\MunicipalityContractResolverInterface;
use App\Contract\Application\Interfaces\Service\ContractRemainingDaysCalculatorServiceInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractAdjustmentsUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractExecutionDeadlineUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractSummaryUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractValueAdditivesUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\SearchContractUsecaseInterface;
use App\Contract\Application\Resolver\MunicipalityContractResolver;
use App\Contract\Application\Service\ContractRemainingDaysCalculatorService;
use App\Contract\Application\Usecase\FindContractAdjustmentsUsecase;
use App\Contract\Application\Usecase\FindContractExecutionDeadlineUsecase;
use App\Contract\Application\Usecase\FindContractSummaryUsecase;
use App\Contract\Application\Usecase\FindContractValueAdditivesUsecase;
use App\Contract\Application\Usecase\SearchContractUsecase;
use App\Contract\Domain\Repository\ContractExecutionDeadlineRepositoryInterface;
use App\Contract\Domain\Repository\ContractReadjustmentRepositoryInterface;
use App\Contract\Domain\Repository\ContractRepositoryInterface;
use App\Contract\Domain\Repository\ValueAdditiveRepositoryInterface;
use App\Contract\Infra\Adapter\ContractSheetAdapter;
use App\Contract\Infra\Mapper\ContractExecutionDeadlineSheetMapper;
use App\Contract\Infra\Mapper\ContractReadjustmentSheetMapper;
use App\Contract\Infra\Mapper\ContractSheetMapper;
use App\Contract\Infra\Mapper\ValueAdditiveSheetMapper;
use App\Contract\Infra\Parser\ContractDateParser;
use App\Contract\Infra\Parser\ContractIntegerParser;
use App\Contract\Infra\Parser\ContractMoneyParser;
use App\Contract\Infra\Parser\ContractNullableStringParser;
use App\Contract\Infra\Parser\ContractNumberParser;
use App\Contract\Infra\Parser\ContractRequiredStringParser;
use App\Contract\Infra\Parser\ContractSearchValueParser;
use App\Contract\Infra\Repository\Gateway\ContractGoogleSheetGatewayRepository;
use App\Contract\Infra\Repository\Gateway\ValueAdditiveGoogleSheetGatewayRepository;
use App\Contract\Infra\Repository\SheetRepository\ContractExecutionDeadlineGoogleSheetRepository;
use App\Contract\Infra\Repository\SheetRepository\ContractReadjustmentGoogleSheetRepository;
use Illuminate\Support\ServiceProvider;

class ContractServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContractSheetAdapterInterface::class, ContractSheetAdapter::class);
        $this->app->bind(ContractDateParserInterface::class, ContractDateParser::class);
        $this->app->bind(ContractIntegerParserInterface::class, ContractIntegerParser::class);
        $this->app->bind(ContractMoneyParserInterface::class, ContractMoneyParser::class);
        $this->app->bind(ContractNullableStringParserInterface::class, ContractNullableStringParser::class);
        $this->app->bind(ContractNumberParserInterface::class, ContractNumberParser::class);
        $this->app->bind(ContractRequiredStringParserInterface::class, ContractRequiredStringParser::class);
        $this->app->bind(ContractSearchValueParserInterface::class, ContractSearchValueParser::class);
        $this->app->bind(ContractExecutionDeadlineSheetMapperInterface::class, ContractExecutionDeadlineSheetMapper::class);
        $this->app->bind(ContractReadjustmentSheetMapperInterface::class, ContractReadjustmentSheetMapper::class);
        $this->app->bind(ContractSheetMapperInterface::class, ContractSheetMapper::class);
        $this->app->bind(ValueAdditiveSheetMapperInterface::class, ValueAdditiveSheetMapper::class);
        $this->app->bind(ContractSummaryAssemblerInterface::class, ContractSummaryAssembler::class);
        $this->app->bind(MunicipalityContractResolverInterface::class, MunicipalityContractResolver::class);
        $this->app->bind(ContractRemainingDaysCalculatorServiceInterface::class, ContractRemainingDaysCalculatorService::class);
        $this->app->bind(FindContractAdjustmentsUsecaseInterface::class, FindContractAdjustmentsUsecase::class);
        $this->app->bind(FindContractExecutionDeadlineUsecaseInterface::class, FindContractExecutionDeadlineUsecase::class);
        $this->app->bind(FindContractSummaryUsecaseInterface::class, FindContractSummaryUsecase::class);
        $this->app->bind(FindContractValueAdditivesUsecaseInterface::class, FindContractValueAdditivesUsecase::class);
        $this->app->bind(SearchContractUsecaseInterface::class, SearchContractUsecase::class);
        $this->app->bind(ContractRepositoryInterface::class, ContractGoogleSheetGatewayRepository::class);
        $this->app->bind(ValueAdditiveRepositoryInterface::class, ValueAdditiveGoogleSheetGatewayRepository::class);
        $this->app->bind(ContractReadjustmentRepositoryInterface::class, ContractReadjustmentGoogleSheetRepository::class);
        $this->app->bind(ContractExecutionDeadlineRepositoryInterface::class, ContractExecutionDeadlineGoogleSheetRepository::class);
    }
}
