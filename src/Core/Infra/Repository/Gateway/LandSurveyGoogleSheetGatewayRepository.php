<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\Gateway;

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Domain\Repository\LandSurveyRepositoryInterface;
use App\Core\Infra\Repository\SheetRepository\FindAllLandSurveyGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByForceGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByLandStatusGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByMunicipalityGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByProcessGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByProgressGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByRegionGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\SearchLandSurveyGoogleSheetRepository;

final readonly class LandSurveyGoogleSheetGatewayRepository implements LandSurveyRepositoryInterface
{
    public function __construct(
        private FindAllLandSurveyGoogleSheetRepository $findAllRepository,
        private SearchLandSurveyGoogleSheetRepository $searchRepository,
        private FindLandSurveyByMunicipalityGoogleSheetRepository $findByMunicipalityRepository,
        private FindLandSurveyByProcessGoogleSheetRepository $findByProcessRepository,
        private FindLandSurveyByForceGoogleSheetRepository $findByForceRepository,
        private FindLandSurveyByRegionGoogleSheetRepository $findByRegionRepository,
        private FindLandSurveyByLandStatusGoogleSheetRepository $findByLandStatusRepository,
        private FindLandSurveyByProgressGoogleSheetRepository $findByProgressRepository,
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

    public function findByProcess(string $process): ?LandSurveyEntity
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
