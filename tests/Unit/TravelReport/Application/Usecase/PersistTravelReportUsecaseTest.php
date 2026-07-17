<?php

use App\Core\TravelReport\Application\DTO\PersistTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Usecase\PersistTravelReportUsecase;
use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;

it('persists a travel report attachment through the repository contract', function (): void {
    $repository = new class implements TravelReportRepositoryInterface
    {
        public ?TravelReportEntity $inserted = null;

        public function insert(TravelReportEntity $travelReport): TravelReportEntity
        {
            $this->inserted = $travelReport;

            return new TravelReportEntity(
                id: 15,
                municipalityId: $travelReport->municipalityId,
                submittedByUserId: $travelReport->submittedByUserId,
                fileName: $travelReport->fileName,
                filePath: $travelReport->filePath,
                fileSize: $travelReport->fileSize,
                mimeType: $travelReport->mimeType,
                seiProcess: $travelReport->seiProcess,
                createdAt: $travelReport->createdAt,
                updatedAt: $travelReport->updatedAt,
            );
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

    $output = (new PersistTravelReportUsecase($repository))(
        new PersistTravelReportInputDTO(
            municipalityId: 1,
            submittedByUserId: 'user-1',
            fileName: 'relatorio.pdf',
            filePath: 'travel-reports/relatorio.pdf',
            seiProcess: 'SEI-12345',
            fileSize: 2048,
            mimeType: 'application/pdf',
        ),
    );

    expect($repository->inserted)->toBeInstanceOf(TravelReportEntity::class)
        ->and($repository->inserted->id)->toBeNull()
        ->and($repository->inserted->municipalityId)->toBe(1)
        ->and($repository->inserted->submittedByUserId)->toBe('user-1')
        ->and($repository->inserted->fileName)->toBe('relatorio.pdf')
        ->and($repository->inserted->filePath)->toBe('travel-reports/relatorio.pdf')
        ->and($repository->inserted->fileSize)->toBe(2048)
        ->and($repository->inserted->mimeType)->toBe('application/pdf')
        ->and($repository->inserted->seiProcess)->toBe('SEI-12345')
        ->and($output)->toBeInstanceOf(PersistTravelReportOutputDTO::class)
        ->and($output->id)->toBe(15)
        ->and($output->municipalityId)->toBe(1)
        ->and($output->submittedByUserId)->toBe('user-1')
        ->and($output->fileName)->toBe('relatorio.pdf')
        ->and($output->filePath)->toBe('travel-reports/relatorio.pdf')
        ->and($output->fileSize)->toBe(2048)
        ->and($output->mimeType)->toBe('application/pdf')
        ->and($output->seiProcess)->toBe('SEI-12345');
});
