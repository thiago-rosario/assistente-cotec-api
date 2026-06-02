<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\Gateway;

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Domain\Repository\TravelItineraryRepositoryInterface;
use App\Core\Infra\Repository\SheetRepository\FindAllTravelItineraryGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByForceGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByLandStatusGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByMunicipalityGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByProcessGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByProgressGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByRegionGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByRequesterGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\SearchTravelItineraryGoogleSheetRepository;

final readonly class TravelItineraryGoogleSheetGatewayRepository implements TravelItineraryRepositoryInterface
{
    public function __construct(
        private FindAllTravelItineraryGoogleSheetRepository $findAllRepository,
        private SearchTravelItineraryGoogleSheetRepository $searchRepository,
        private FindTravelItineraryByMunicipalityGoogleSheetRepository $findByMunicipalityRepository,
        private FindTravelItineraryByProcessGoogleSheetRepository $findByProcessRepository,
        private FindTravelItineraryByForceGoogleSheetRepository $findByForceRepository,
        private FindTravelItineraryByRegionGoogleSheetRepository $findByRegionRepository,
        private FindTravelItineraryByLandStatusGoogleSheetRepository $findByLandStatusRepository,
        private FindTravelItineraryByProgressGoogleSheetRepository $findByProgressRepository,
        private FindTravelItineraryByRequesterGoogleSheetRepository $findByRequesterRepository,
    ) {}

    public function all(): array
    {
        return $this->findAllRepository->findAllSheet();
    }

    public function search(string $term): array
    {
        return $this->searchRepository->search($this->findAllRepository->findAllSheet(), $term);
    }

    public function findByMunicipality(string $municipality): array
    {
        return $this->findByMunicipalityRepository->findByMunicipality(
            $this->findAllRepository->findAllSheet(),
            $municipality,
        );
    }

    public function findByProcess(string $process): ?TravelItineraryEntity
    {
        return $this->findByProcessRepository->findByProcess(
            $this->findAllRepository->findAllSheet(),
            $process,
        );
    }

    public function findByForce(string $force): array
    {
        return $this->findByForceRepository->findByForce($this->findAllRepository->findAllSheet(), $force);
    }

    public function findByRegion(string $region): array
    {
        return $this->findByRegionRepository->findByRegion($this->findAllRepository->findAllSheet(), $region);
    }

    public function findByLandStatus(string $status): array
    {
        return $this->findByLandStatusRepository->findByLandStatus(
            $this->findAllRepository->findAllSheet(),
            $status,
        );
    }

    public function findByProgress(string $progress): array
    {
        return $this->findByProgressRepository->findByProgress(
            $this->findAllRepository->findAllSheet(),
            $progress,
        );
    }

    public function findByRequester(string $requester): array
    {
        return $this->findByRequesterRepository->findByRequester(
            $this->findAllRepository->findAllSheet(),
            $requester,
        );
    }
}
