<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Providers;

use App\TechnicalInspectionReport\Application\Builder\TechnicalInspectionReportDraftBuilder;
use App\TechnicalInspectionReport\Application\Factory\RegisterTechnicalInspectionReportCatalogInputDTOFactory;
use App\TechnicalInspectionReport\Application\Factory\TechnicalInspectionReportDraftFactory;
use App\TechnicalInspectionReport\Application\Factory\TechnicalInspectionReportGoogleSheetFactory;
use App\TechnicalInspectionReport\Application\Interfaces\Builder\TechnicalInspectionReportDraftBuilderInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Factory\RegisterTechnicalInspectionReportCatalogInputDTOFactoryInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Factory\TechnicalInspectionReportGoogleSheetFactoryInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Mapper\TechnicalInspectionReportDraftMapperInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Service\TechnicalInspectionReportWhatsappConversationFlowServiceInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Storage\TechnicalInspectionReportDocumentTemporaryStorageInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Storage\TechnicalInspectionReportFileStorageInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\FindTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\StoreTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Application\Service\TechnicalInspectionReportWhatsappConversationFlowService;
use App\TechnicalInspectionReport\Application\Usecase\FindTechnicalInspectionReportUsecase;
use App\TechnicalInspectionReport\Application\Usecase\StoreTechnicalInspectionReportUsecase;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportDraftRepositoryInterface;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportDriveRepositoryInterface;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportSheetRepositoryInterface;
use App\TechnicalInspectionReport\Infra\External\GoogleDriveTechnicalInspectionReportFileStorage;
use App\TechnicalInspectionReport\Infra\Mapper\TechnicalInspectionReportDraftMapper;
use App\TechnicalInspectionReport\Infra\Repository\CacheTechnicalInspectionReportDraftRepository;
use App\TechnicalInspectionReport\Infra\Repository\Gateway\TechnicalInspectionReportGoogleDriveGatewayRepository;
use App\TechnicalInspectionReport\Infra\Repository\Gateway\TechnicalInspectionReportGoogleSheetGatewayRepository;
use App\TechnicalInspectionReport\Infra\Storage\LocalTechnicalInspectionReportDocumentTemporaryStorage;
use Illuminate\Support\ServiceProvider;

final class TechnicalInspectionReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TechnicalInspectionReportDraftBuilderInterface::class,
            TechnicalInspectionReportDraftBuilder::class,
        );
        $this->app->bind(
            TechnicalInspectionReportDraftMapperInterface::class,
            TechnicalInspectionReportDraftMapper::class,
        );
        $this->app->bind(
            RegisterTechnicalInspectionReportCatalogInputDTOFactoryInterface::class,
            RegisterTechnicalInspectionReportCatalogInputDTOFactory::class,
        );
        $this->app->bind(
            TechnicalInspectionReportGoogleSheetFactoryInterface::class,
            TechnicalInspectionReportGoogleSheetFactory::class,
        );
        $this->app->bind(
            FindTechnicalInspectionReportUsecaseInterface::class,
            FindTechnicalInspectionReportUsecase::class,
        );
        $this->app->bind(
            StoreTechnicalInspectionReportUsecaseInterface::class,
            StoreTechnicalInspectionReportUsecase::class,
        );
        $this->app->bind(TechnicalInspectionReportDraftFactory::class);
        $this->app->bind(
            TechnicalInspectionReportDraftRepositoryInterface::class,
            CacheTechnicalInspectionReportDraftRepository::class,
        );
        $this->app->bind(
            TechnicalInspectionReportDocumentTemporaryStorageInterface::class,
            LocalTechnicalInspectionReportDocumentTemporaryStorage::class,
        );
        $this->app->bind(
            TechnicalInspectionReportWhatsappConversationFlowServiceInterface::class,
            TechnicalInspectionReportWhatsappConversationFlowService::class,
        );
        $this->app->bind(
            TechnicalInspectionReportFileStorageInterface::class,
            GoogleDriveTechnicalInspectionReportFileStorage::class,
        );
        $this->app->bind(
            TechnicalInspectionReportSheetRepositoryInterface::class,
            TechnicalInspectionReportGoogleSheetGatewayRepository::class,
        );
        $this->app->bind(
            TechnicalInspectionReportDriveRepositoryInterface::class,
            TechnicalInspectionReportGoogleDriveGatewayRepository::class,
        );
    }
}
