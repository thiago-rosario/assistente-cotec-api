<?php

use App\Core\TravelReport\Application\DTO\DeleteTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\DeleteTravelReportOutputDTO;
use App\Core\TravelReport\Application\Usecase\DeleteTravelReportUsecase;
use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;

it('deletes a travel report by id through the repository contract', function (): void {
    $repository = new class implements TravelReportRepositoryInterface
    {
        public int $deletedId = 0;

        public function insert(TravelReportEntity $travelReport): TravelReportEntity
        {
            return $travelReport;
        }

        public function findById(int $id): ?TravelReportEntity
        {
            return null;
        }

        public function all(): array
        {
            return [];
        }

        public function findBySeiProcess(string $seiProcess): ?TravelReportEntity
        {
            return null;
        }

        public function findBySubmittedByUserId(string $submittedByUserId): array
        {
            return [];
        }

        public function findByMunicipalityId(int $municipalityId): array
        {
            return [];
        }

        public function delete(int $id): bool
        {
            $this->deletedId = $id;

            return true;
        }
    };

    $output = (new DeleteTravelReportUsecase($repository))(
        new DeleteTravelReportInputDTO(id: 15),
    );

    expect($repository->deletedId)->toBe(15)
        ->and($output)->toBeInstanceOf(DeleteTravelReportOutputDTO::class)
        ->and($output->id)->toBe(15)
        ->and($output->deleted)->toBeTrue();
});
