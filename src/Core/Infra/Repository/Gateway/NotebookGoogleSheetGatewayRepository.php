<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\Gateway;

use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Domain\Repository\NotebookRepositoryInterface;
use App\Core\Infra\Repository\SheetRepository\FindAllNotebookGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindNotebookByLandStatusGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindNotebookByMunicipalityGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindNotebookByRelatedProcessGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindNotebookByRequesterGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\SearchNotebookGoogleSheetRepository;

final readonly class NotebookGoogleSheetGatewayRepository implements NotebookRepositoryInterface
{
    public function __construct(
        private FindAllNotebookGoogleSheetRepository $findAllRepository,
        private SearchNotebookGoogleSheetRepository $searchRepository,
        private FindNotebookByMunicipalityGoogleSheetRepository $findByMunicipalityRepository,
        private FindNotebookByRelatedProcessGoogleSheetRepository $findByRelatedProcessRepository,
        private FindNotebookByRequesterGoogleSheetRepository $findByRequesterRepository,
        private FindNotebookByLandStatusGoogleSheetRepository $findByLandStatusRepository,
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

    public function findByRelatedProcess(string $process): ?NotebookEntity
    {
        return $this->findByRelatedProcessRepository->findByRelatedProcess(
            $this->findAllRepository->findAllSheet(),
            $process,
        );
    }

    public function findByRequester(string $requester): array
    {
        return $this->findByRequesterRepository->findByRequester($this->findAllRepository->findAllSheet(), $requester);
    }

    public function findByLandStatus(string $status): array
    {
        return $this->findByLandStatusRepository->findByLandStatus(
            $this->findAllRepository->findAllSheet(),
            $status,
        );
    }
}
