<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\Gateway;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportDriveRepositoryInterface;
use App\TechnicalInspectionReport\Domain\ValueObject\ExternalMessageIdValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\DeleteTechnicalInspectionReportDriveRepository;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\ExistsTechnicalInspectionReportByExternalMessageIdDriveRepository;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\FindAllTechnicalInspectionReportDriveRepository;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\FindTechnicalInspectionReportByIdDriveRepository;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\FindTechnicalInspectionReportByMunicipalityDriveRepository;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\SaveTechnicalInspectionReportDriveRepository;

final readonly class TechnicalInspectionReportGoogleDriveGatewayRepository implements TechnicalInspectionReportDriveRepositoryInterface
{
    public function __construct(
        private SaveTechnicalInspectionReportDriveRepository $saveRepository,
        private FindTechnicalInspectionReportByIdDriveRepository $findByIdRepository,
        private ExistsTechnicalInspectionReportByExternalMessageIdDriveRepository $existsByExternalMessageIdRepository,
        private FindAllTechnicalInspectionReportDriveRepository $findAllRepository,
        private FindTechnicalInspectionReportByMunicipalityDriveRepository $findByMunicipalityRepository,
        private DeleteTechnicalInspectionReportDriveRepository $deleteRepository,
    ) {}

    public function save(TechnicalInspectionReportEntity $report): void
    {
        $this->saveRepository->save($report);
    }

    public function findById(TechnicalInspectionReportIdValueObject $id): ?TechnicalInspectionReportEntity
    {
        return $this->findByIdRepository->findById($id);
    }

    public function existsByExternalMessageId(ExternalMessageIdValueObject $externalMessageId): bool
    {
        return $this->existsByExternalMessageIdRepository->existsByExternalMessageId($externalMessageId);
    }

    /**
     * @return list<TechnicalInspectionReportEntity>
     */
    public function findAll(): array
    {
        return $this->findAllRepository->findAll();
    }

    /**
     * @return list<TechnicalInspectionReportEntity>
     */
    public function findByMunicipality(string $municipality): array
    {
        return $this->findByMunicipalityRepository->findByMunicipality(
            $this->findAllRepository->findAll(),
            $municipality,
        );
    }

    public function delete(TechnicalInspectionReportIdValueObject $id): void
    {
        $this->deleteRepository->delete($id);
    }
}
