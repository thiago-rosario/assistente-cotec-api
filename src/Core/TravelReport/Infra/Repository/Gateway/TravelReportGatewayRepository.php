<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Repository\Gateway;

use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\DeleteTravelReportEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\FindAllTravelReportsEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\FindTravelReportByIdEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\FindTravelReportByMunicipalityIdEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\FindTravelReportBySeiProcessEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\FindTravelReportBySubmittedByUserIdEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\TravelReportInsertEloquentRepository;

readonly class TravelReportGatewayRepository implements TravelReportRepositoryInterface
{
    public function __construct(
        private readonly TravelReportInsertEloquentRepository $travelReportInsertRepository,
        private readonly FindTravelReportByIdEloquentRepository $findTravelReportByIdRepository,
        private readonly FindAllTravelReportsEloquentRepository $findAllTravelReportsRepository,
        private readonly FindTravelReportBySeiProcessEloquentRepository $findTravelReportBySeiProcessRepository,
        private readonly FindTravelReportBySubmittedByUserIdEloquentRepository $findTravelReportBySubmittedByUserIdRepository,
        private readonly FindTravelReportByMunicipalityIdEloquentRepository $findTravelReportByMunicipalityIdRepository,
        private readonly DeleteTravelReportEloquentRepository $deleteTravelReportRepository,
    ) {}

    public function insert(TravelReportEntity $travelReport): TravelReportEntity
    {
        return $this->travelReportInsertRepository->insert($travelReport);
    }

    public function findById(int $id): ?TravelReportEntity
    {
        return $this->findTravelReportByIdRepository->findById($id);
    }

    public function all(): array
    {
        return $this->findAllTravelReportsRepository->all();
    }

    public function findBySeiProcess(string $seiProcess): ?TravelReportEntity
    {
        return $this->findTravelReportBySeiProcessRepository->findBySeiProcess($seiProcess);
    }

    public function findBySubmittedByUserId(string $submittedByUserId): array
    {
        return $this->findTravelReportBySubmittedByUserIdRepository->findBySubmittedByUserId($submittedByUserId);
    }

    public function findByMunicipalityId(int $municipalityId): array
    {
        return $this->findTravelReportByMunicipalityIdRepository->findByMunicipalityId($municipalityId);
    }

    public function delete(int $id): bool
    {
        return $this->deleteTravelReportRepository->delete($id);
    }
}
