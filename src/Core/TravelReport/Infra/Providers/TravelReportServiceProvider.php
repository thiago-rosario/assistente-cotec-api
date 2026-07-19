<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Providers;

use App\Core\TravelReport\Application\Interface\Usecase\DeleteTravelReportUsecaseInterface;
use App\Core\TravelReport\Application\Interface\Usecase\FindTravelReportBySeiProcessUsecaseInterface;
use App\Core\TravelReport\Application\Interface\Usecase\ListTravelReportByMunicipalityIdUsecaseInterface;
use App\Core\TravelReport\Application\Interface\Usecase\ListTravelReportsUsecaseInterface;
use App\Core\TravelReport\Application\Interface\Usecase\PersistTravelReportUsecaseInterface;
use App\Core\TravelReport\Application\Usecase\DeleteTravelReportUsecase;
use App\Core\TravelReport\Application\Usecase\FindTravelReportBySeiProcessUsecase;
use App\Core\TravelReport\Application\Usecase\ListTravelReportByMunicipalityIdUsecase;
use App\Core\TravelReport\Application\Usecase\ListTravelReportsUsecase;
use App\Core\TravelReport\Application\Usecase\PersistTravelReportUsecase;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;
use App\Core\TravelReport\Infra\Repository\Gateway\TravelReportGatewayRepository;
use Illuminate\Support\ServiceProvider;

class TravelReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TravelReportRepositoryInterface::class, TravelReportGatewayRepository::class);
        $this->app->bind(PersistTravelReportUsecaseInterface::class, PersistTravelReportUsecase::class);
        $this->app->bind(ListTravelReportsUsecaseInterface::class, ListTravelReportsUsecase::class);
        $this->app->bind(ListTravelReportByMunicipalityIdUsecaseInterface::class, ListTravelReportByMunicipalityIdUsecase::class);
        $this->app->bind(FindTravelReportBySeiProcessUsecaseInterface::class, FindTravelReportBySeiProcessUsecase::class);
        $this->app->bind(DeleteTravelReportUsecaseInterface::class, DeleteTravelReportUsecase::class);
    }

    public function boot(): void {}
}
