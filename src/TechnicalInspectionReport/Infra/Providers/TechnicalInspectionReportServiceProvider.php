<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Providers;

use App\TechnicalInspectionReport\Application\Factory\TechnicalInspectionReportGoogleSheetFactory;
use App\TechnicalInspectionReport\Application\Interfaces\Factory\TechnicalInspectionReportGoogleSheetFactoryInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\StoreTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Application\Usecase\StoreTechnicalInspectionReportUsecase;
use Illuminate\Support\ServiceProvider;

final class TechnicalInspectionReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TechnicalInspectionReportGoogleSheetFactoryInterface::class,
            TechnicalInspectionReportGoogleSheetFactory::class,
        );
        $this->app->bind(
            StoreTechnicalInspectionReportUsecaseInterface::class,
            StoreTechnicalInspectionReportUsecase::class,
        );
    }
}
