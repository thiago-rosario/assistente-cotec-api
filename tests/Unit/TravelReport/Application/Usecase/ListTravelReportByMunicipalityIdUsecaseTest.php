<?php

use App\Core\TravelReport\Application\DTO\ListTravelReportByMunicipalityIdInputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportsOutputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Usecase\ListTravelReportByMunicipalityIdUsecase;
use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;

it('lists travel reports by municipality id through the repository contract', function (): void {
    $repository = new class implements TravelReportRepositoryInterface
    {
        public int $queriedMunicipalityId = 0;

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
            $this->queriedMunicipalityId = $municipalityId;

            return [
                new TravelReportEntity(
                    id: 10,
                    municipalityId: $municipalityId,
                    submittedByUserId: 'user-1',
                    fileName: 'relatorio-1.pdf',
                    filePath: 'travel-reports/relatorio-1.pdf',
                    fileSize: 1024,
                    mimeType: 'application/pdf',
                    seiProcess: 'SEI-001',
                ),
                new TravelReportEntity(
                    id: 11,
                    municipalityId: $municipalityId,
                    submittedByUserId: 'user-2',
                    fileName: 'relatorio-2.pdf',
                    filePath: 'travel-reports/relatorio-2.pdf',
                    fileSize: 2048,
                    mimeType: 'application/pdf',
                    seiProcess: 'SEI-002',
                ),
            ];
        }

        public function delete(int $id): bool
        {
            return false;
        }
    };

    $output = (new ListTravelReportByMunicipalityIdUsecase($repository))(
        new ListTravelReportByMunicipalityIdInputDTO(municipalityId: 33),
    );

    expect($repository->queriedMunicipalityId)->toBe(33)
        ->and($output)->toBeInstanceOf(ListTravelReportsOutputDTO::class)
        ->and($output->total)->toBe(2)
        ->and($output->data)->toHaveCount(2)
        ->and($output->data[0])->toBeInstanceOf(PersistTravelReportOutputDTO::class)
        ->and($output->data[0]->id)->toBe(10)
        ->and($output->data[0]->municipalityId)->toBe(33)
        ->and($output->data[0]->seiProcess)->toBe('SEI-001')
        ->and($output->data[1]->id)->toBe(11)
        ->and($output->data[1]->fileName)->toBe('relatorio-2.pdf');
});
