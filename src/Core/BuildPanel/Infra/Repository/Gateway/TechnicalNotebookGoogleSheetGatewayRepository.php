<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Infra\Repository\Gateway;

use App\Core\BuildPanel\Domain\Entity\TechnicalNotebookEntity;
use App\Core\BuildPanel\Domain\Repository\TechnicalNotebookRepositoryInterface;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\FindAllTechnicalNotebookGoogleSheetRepository;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\FindTechnicalNotebookByBuildStatusGoogleSheetRepository;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\FindTechnicalNotebookByForceGoogleSheetRepository;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\FindTechnicalNotebookByMunicipalityGoogleSheetRepository;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\FindTechnicalNotebookByProcessGoogleSheetRepository;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\SearchTechnicalNotebookGoogleSheetRepository;

final readonly class TechnicalNotebookGoogleSheetGatewayRepository implements TechnicalNotebookRepositoryInterface
{
    public function __construct(
        private FindAllTechnicalNotebookGoogleSheetRepository $findAllRepository,
        private SearchTechnicalNotebookGoogleSheetRepository $searchRepository,
        private FindTechnicalNotebookByMunicipalityGoogleSheetRepository $findByMunicipalityRepository,
        private FindTechnicalNotebookByProcessGoogleSheetRepository $findByProcessRepository,
        private FindTechnicalNotebookByForceGoogleSheetRepository $findByForceRepository,
        private FindTechnicalNotebookByBuildStatusGoogleSheetRepository $findByBuildStatusRepository,
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

    public function findByProcess(string $process): ?TechnicalNotebookEntity
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

    public function findByBuildStatus(string $status): array
    {
        return $this->findByBuildStatusRepository->findByBuildStatus(
            $this->findAllRepository->findAllSheet(),
            $status,
        );
    }
}
