<?php

use App\Core\TravelReport\Application\DTO\ListTravelReportsInputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportsOutputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Usecase\ListTravelReportsUsecase;
use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;

it('lists all travel reports through the repository contract', function (): void {
    $repository = new class implements TravelReportRepositoryInterface
    {
        public bool $listedAll = false;

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
            $this->listedAll = true;

            return [
                new TravelReportEntity(
                    id: 10,
                    municipalityId: 1,
                    submittedByUserId: 'user-1',
                    fileName: 'relatorio-1.pdf',
                    filePath: 'travel-reports/relatorio-1.pdf',
                    fileSize: 1024,
                    mimeType: 'application/pdf',
                    seiProcess: 'SEI-001',
                ),
                new TravelReportEntity(
                    id: 11,
                    municipalityId: 2,
                    submittedByUserId: 'user-2',
                    fileName: 'relatorio-2.pdf',
                    filePath: 'travel-reports/relatorio-2.pdf',
                    fileSize: 2048,
                    mimeType: 'application/pdf',
                    seiProcess: 'SEI-002',
                ),
            ];
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

    $output = (new ListTravelReportsUsecase($repository))(new ListTravelReportsInputDTO);

    expect($repository->listedAll)->toBeTrue()
        ->and($output)->toBeInstanceOf(ListTravelReportsOutputDTO::class)
        ->and($output->total)->toBe(2)
        ->and($output->data)->toHaveCount(2)
        ->and($output->data[0])->toBeInstanceOf(PersistTravelReportOutputDTO::class)
        ->and($output->data[0]->id)->toBe(10)
        ->and($output->data[1]->municipalityId)->toBe(2)
        ->and($output->data[1]->seiProcess)->toBe('SEI-002');
});
