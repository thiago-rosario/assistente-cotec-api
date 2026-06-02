<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\Gateway;

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Domain\Repository\ConstructionDemandRepositoryInterface;
use App\Core\Infra\Repository\SheetRepository\FindAllConstructionDemandGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByForceGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByLandStatusGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByMunicipalityGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByProcessGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByProgressGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByRegionGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\SearchConstructionDemandGoogleSheetRepository;

final readonly class ConstructionDemandGoogleSheetGatewayRepository implements ConstructionDemandRepositoryInterface
{
    public function __construct(
        private FindAllConstructionDemandGoogleSheetRepository $findAllRepository,
        private SearchConstructionDemandGoogleSheetRepository $searchRepository,
        private FindConstructionDemandByMunicipalityGoogleSheetRepository $findByMunicipalityRepository,
        private FindConstructionDemandByProcessGoogleSheetRepository $findByProcessRepository,
        private FindConstructionDemandByForceGoogleSheetRepository $findByForceRepository,
        private FindConstructionDemandByRegionGoogleSheetRepository $findByRegionRepository,
        private FindConstructionDemandByLandStatusGoogleSheetRepository $findByLandStatusRepository,
        private FindConstructionDemandByProgressGoogleSheetRepository $findByProgressRepository,
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

    public function findByProcess(string $process): ?ConstructionDemandEntity
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
}
