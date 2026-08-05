<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Providers;

use App\TechnicalInspectionReport\Application\Factory\RegisterTechnicalInspectionReportCatalogInputDTOFactory;
use App\TechnicalInspectionReport\Application\Factory\TechnicalInspectionReportGoogleSheetFactory;
use App\TechnicalInspectionReport\Application\Interfaces\Factory\RegisterTechnicalInspectionReportCatalogInputDTOFactoryInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Factory\TechnicalInspectionReportGoogleSheetFactoryInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Storage\TechnicalInspectionReportFileStorageInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\FindTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\StoreTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Application\Usecase\FindTechnicalInspectionReportUsecase;
use App\TechnicalInspectionReport\Application\Usecase\StoreTechnicalInspectionReportUsecase;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportDriveRepositoryInterface;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportSheetRepositoryInterface;
use App\TechnicalInspectionReport\Infra\External\GoogleDriveTechnicalInspectionReportFileStorage;
use App\TechnicalInspectionReport\Infra\Repository\Gateway\TechnicalInspectionReportGoogleDriveGatewayRepository;
use App\TechnicalInspectionReport\Infra\Repository\Gateway\TechnicalInspectionReportGoogleSheetGatewayRepository;
use Illuminate\Support\ServiceProvider;

final class TechnicalInspectionReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
