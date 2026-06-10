<?php

use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Infra\Repository\Gateway\NotebookGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\SheetRepository\FindAllNotebookGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindNotebookByLandStatusGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindNotebookByMunicipalityGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindNotebookByRelatedProcessGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindNotebookByRequesterGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\SearchNotebookGoogleSheetRepository;

it('delegates notebook gateway methods to their specific repositories', function () {
    $allRepository = new class extends FindAllNotebookGoogleSheetRepository
    {
        public bool $called = false;

        public function __construct() {}

        public function findAllSheet(): array
        {
            $this->called = true;

            return [notebookGatewayEntity(relatedProcess: '001.7313.2023.0006626-49')];
        }
    };

    $searchRepository = new class extends SearchNotebookGoogleSheetRepository
    {
        public ?string $term = null;

        public array $receivedNotebooks = [];

        public function search(array $notebooks, string $term): array
        {
            $this->receivedNotebooks = $notebooks;
            $this->term = $term;

            return [notebookGatewayEntity(unitClaim: $term)];
        }
    };

    $municipalityRepository = new class extends FindNotebookByMunicipalityGoogleSheetRepository
    {
        public ?string $municipality = null;

        public array $receivedNotebooks = [];

        public function findByMunicipality(array $notebooks, string $municipality): array
        {
            $this->receivedNotebooks = $notebooks;
            $this->municipality = $municipality;

            return [notebookGatewayEntity(municipality: $municipality)];
        }
    };

    $relatedProcessRepository = new class extends FindNotebookByRelatedProcessGoogleSheetRepository
    {
        public ?string $process = null;

        public array $receivedNotebooks = [];

        public function findByRelatedProcess(array $notebooks, string $process): ?NotebookEntity
        {
            $this->receivedNotebooks = $notebooks;
            $this->process = $process;

            return notebookGatewayEntity(relatedProcess: $process);
        }
    };

    $requesterRepository = new class extends FindNotebookByRequesterGoogleSheetRepository
    {
        public ?string $requester = null;

        public array $receivedNotebooks = [];

        public function findByRequester(array $notebooks, string $requester): array
        {
            $this->receivedNotebooks = $notebooks;
            $this->requester = $requester;

            return [notebookGatewayEntity(requester: $requester)];
        }
    };

    $landStatusRepository = new class extends FindNotebookByLandStatusGoogleSheetRepository
    {
        public ?string $status = null;

        public array $receivedNotebooks = [];

        public function findByLandStatus(array $notebooks, string $status): array
        {
            $this->receivedNotebooks = $notebooks;
            $this->status = $status;

            return [notebookGatewayEntity(landStatus: $status)];
        }
    };

    $gateway = new NotebookGoogleSheetGatewayRepository(
        $allRepository,
        $searchRepository,
        $municipalityRepository,
        $relatedProcessRepository,
        $requesterRepository,
        $landStatusRepository,
    );

    expect($gateway->all())->toHaveCount(1)
        ->and($allRepository->called)->toBeTrue()
        ->and($gateway->search('delegacia'))->toHaveCount(1)
        ->and($searchRepository->term)->toBe('delegacia')
        ->and($searchRepository->receivedNotebooks)->toHaveCount(1)
        ->and($gateway->findByMunicipality('Acajutiba'))->toHaveCount(1)
        ->and($municipalityRepository->municipality)->toBe('Acajutiba')
        ->and($municipalityRepository->receivedNotebooks)->toHaveCount(1)
        ->and($gateway->findByRelatedProcess('001.7313.2023.0006626-49')?->relatedProcess)->toBe('001.7313.2023.0006626-49')
        ->and($relatedProcessRepository->process)->toBe('001.7313.2023.0006626-49')
        ->and($relatedProcessRepository->receivedNotebooks)->toHaveCount(1)
        ->and($gateway->findByRequester('Prefeitura'))->toHaveCount(1)
        ->and($requesterRepository->requester)->toBe('Prefeitura')
        ->and($requesterRepository->receivedNotebooks)->toHaveCount(1)
        ->and($gateway->findByLandStatus('Terreno doado'))->toHaveCount(1)
        ->and($landStatusRepository->status)->toBe('Terreno doado')
        ->and($landStatusRepository->receivedNotebooks)->toHaveCount(1);
});

it('returns all notebook row information when finding by related process', function () {
    $notebooks = [
        notebookGatewayEntity(
            municipality: 'Acajutiba',
            relatedProcess: '001.7313.2023.0006626-49 020.2301.2022.0007756-88',
            unitClaim: 'Delegacia',
            landStatus: 'Terreno doado',
            requester: 'Prefeitura',
        ),
        notebookGatewayEntity(
            municipality: 'Catu',
            relatedProcess: '020.2301.2022.0007756-88',
            unitClaim: 'CIPM',
            landStatus: 'Aguardando visita técnica',
            requester: 'Comando Região Leste',
        ),
    ];

    $repository = new FindNotebookByRelatedProcessGoogleSheetRepository;

    $notebook = $repository->findByRelatedProcess($notebooks, '020.2301.2022.0007756-88');

    expect($notebook)->toBeInstanceOf(NotebookEntity::class)
        ->and($notebook->relatedProcess)->toBe('001.7313.2023.0006626-49 020.2301.2022.0007756-88')
        ->and($notebook->municipality)->toBe('Acajutiba')
        ->and($notebook->unitClaim)->toBe('Delegacia')
        ->and($notebook->landStatus)->toBe('Terreno doado')
        ->and($notebook->requester)->toBe('Prefeitura')
        ->and($notebook->estimatedCost)->toBe(1539740.33);
});

function notebookGatewayEntity(
    string $municipality = 'Acajutiba',
    ?string $relatedProcess = null,
    ?string $unitClaim = 'Delegacia',
    ?string $landStatus = 'Terreno doado',
    ?string $requester = 'Prefeitura',
): NotebookEntity {
    return new NotebookEntity(
        municipality: $municipality,
        relatedProcess: $relatedProcess,
        unitClaim: $unitClaim,
        objectSize: '1B',
        landStatus: $landStatus,
        requester: $requester,
        estimatedCost: 1539740.33,
    );
}
