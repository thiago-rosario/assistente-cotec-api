<?php

use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessInputDTO;
use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessOutputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Usecase\FindTravelReportBySeiProcessUsecase;
use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;

it('finds a travel report by SEI process through the repository contract', function (): void {
    $repository = new class implements TravelReportRepositoryInterface
    {
        public string $queriedSeiProcess = '';

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
            $this->queriedSeiProcess = $seiProcess;

            return new TravelReportEntity(
                id: 15,
                municipalityId: 1,
                submittedByUserId: 'user-1',
                fileName: 'relatorio.pdf',
                filePath: 'travel-reports/relatorio.pdf',
                fileSize: 2048,
                mimeType: 'application/pdf',
                seiProcess: $seiProcess,
            );
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
            return false;
        }
    };

    $output = (new FindTravelReportBySeiProcessUsecase($repository))(
        new FindTravelReportBySeiProcessInputDTO(seiProcess: 'SEI-12345'),
    );

    expect($repository->queriedSeiProcess)->toBe('SEI-12345')
        ->and($output)->toBeInstanceOf(FindTravelReportBySeiProcessOutputDTO::class)
        ->and($output->data)->toBeInstanceOf(PersistTravelReportOutputDTO::class)
        ->and($output->data->id)->toBe(15)
        ->and($output->data->seiProcess)->toBe('SEI-12345');
});

it('returns null data when no travel report exists for the SEI process', function (): void {
    $repository = new class implements TravelReportRepositoryInterface
    {
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
            return false;
        }
    };

    $output = (new FindTravelReportBySeiProcessUsecase($repository))(
        new FindTravelReportBySeiProcessInputDTO(seiProcess: 'SEI-404'),
    );

    expect($output)->toBeInstanceOf(FindTravelReportBySeiProcessOutputDTO::class)
        ->and($output->data)->toBeNull();
});
