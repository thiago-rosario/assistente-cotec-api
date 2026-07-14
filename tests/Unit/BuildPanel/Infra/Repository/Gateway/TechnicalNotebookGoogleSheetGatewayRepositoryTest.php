<?php

use App\Core\BuildPanel\Domain\Entity\TechnicalNotebookEntity;
use App\Core\BuildPanel\Infra\Repository\Gateway\TechnicalNotebookGoogleSheetGatewayRepository;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\FindAllTechnicalNotebookGoogleSheetRepository;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\FindTechnicalNotebookByBuildStatusGoogleSheetRepository;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\FindTechnicalNotebookByForceGoogleSheetRepository;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\FindTechnicalNotebookByMunicipalityGoogleSheetRepository;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\FindTechnicalNotebookByProcessGoogleSheetRepository;
use App\Core\BuildPanel\Infra\Repository\SheetRepository\SearchTechnicalNotebookGoogleSheetRepository;

it('delegates technical notebook gateway methods to their specific repositories', function () {
    $allRepository = new class extends FindAllTechnicalNotebookGoogleSheetRepository
    {
        public bool $called = false;

        public function __construct() {}

        public function findAllSheet(): array
        {
            $this->called = true;

            return [technicalNotebookGatewayEntity(process: '001.7313.2023.0006626-49')];
        }
    };

    $searchRepository = new class extends SearchTechnicalNotebookGoogleSheetRepository
    {
        public ?string $term = null;

        public array $receivedTechnicalNotebooks = [];

        public function search(array $technicalNotebooks, string $term): array
        {
            $this->receivedTechnicalNotebooks = $technicalNotebooks;
            $this->term = $term;

            return [technicalNotebookGatewayEntity(claim: $term)];
        }
    };

    $municipalityRepository = new class extends FindTechnicalNotebookByMunicipalityGoogleSheetRepository
    {
        public ?string $municipality = null;

        public array $receivedTechnicalNotebooks = [];

        public function findByMunicipality(array $technicalNotebooks, string $municipality): array
        {
            $this->receivedTechnicalNotebooks = $technicalNotebooks;
            $this->municipality = $municipality;

            return [technicalNotebookGatewayEntity(municipality: $municipality)];
        }
    };

    $processRepository = new class extends FindTechnicalNotebookByProcessGoogleSheetRepository
    {
        public ?string $process = null;

        public array $receivedTechnicalNotebooks = [];

        public function findByProcess(array $technicalNotebooks, string $process): ?TechnicalNotebookEntity
        {
            $this->receivedTechnicalNotebooks = $technicalNotebooks;
            $this->process = $process;

            return technicalNotebookGatewayEntity(process: $process);
        }
    };

    $forceRepository = new class extends FindTechnicalNotebookByForceGoogleSheetRepository
    {
        public ?string $force = null;

        public array $receivedTechnicalNotebooks = [];

        public function findByForce(array $technicalNotebooks, string $force): array
        {
            $this->receivedTechnicalNotebooks = $technicalNotebooks;
            $this->force = $force;

            return [technicalNotebookGatewayEntity(force: $force)];
        }
    };

    $buildStatusRepository = new class extends FindTechnicalNotebookByBuildStatusGoogleSheetRepository
    {
        public ?string $status = null;

        public array $receivedTechnicalNotebooks = [];

        public function findByBuildStatus(array $technicalNotebooks, string $status): array
        {
            $this->receivedTechnicalNotebooks = $technicalNotebooks;
            $this->status = $status;

            return [technicalNotebookGatewayEntity(buildStatus: $status)];
        }
    };

    $gateway = new TechnicalNotebookGoogleSheetGatewayRepository(
        $allRepository,
        $searchRepository,
        $municipalityRepository,
        $processRepository,
        $forceRepository,
        $buildStatusRepository,
    );

    expect($gateway->all())->toHaveCount(1)
        ->and($allRepository->called)->toBeTrue()
        ->and($gateway->search('delegacia'))->toHaveCount(1)
        ->and($searchRepository->term)->toBe('delegacia')
        ->and($searchRepository->receivedTechnicalNotebooks)->toHaveCount(1)
        ->and($gateway->findByMunicipality('Acajutiba'))->toHaveCount(1)
        ->and($municipalityRepository->municipality)->toBe('Acajutiba')
        ->and($municipalityRepository->receivedTechnicalNotebooks)->toHaveCount(1)
        ->and($gateway->findByProcess('001.7313.2023.0006626-49')?->process)->toBe('001.7313.2023.0006626-49')
        ->and($processRepository->process)->toBe('001.7313.2023.0006626-49')
        ->and($processRepository->receivedTechnicalNotebooks)->toHaveCount(1)
        ->and($gateway->findByForce('PC'))->toHaveCount(1)
        ->and($forceRepository->force)->toBe('PC')
        ->and($forceRepository->receivedTechnicalNotebooks)->toHaveCount(1)
        ->and($gateway->findByBuildStatus('Em andamento'))->toHaveCount(1)
        ->and($buildStatusRepository->status)->toBe('Em andamento')
        ->and($buildStatusRepository->receivedTechnicalNotebooks)->toHaveCount(1);
});

it('returns all technical notebook row information when finding by sei process', function () {
    $technicalNotebooks = [
        technicalNotebookGatewayEntity(
            municipality: 'Acajutiba',
            process: '001.7313.2023.0006626-49',
            force: 'PC',
            claim: 'Delegacia',
            buildStatus: 'Em andamento',
        ),
        technicalNotebookGatewayEntity(
            municipality: 'Catu',
            process: '020.2301.2022.0007756-88',
            force: 'PM',
            claim: 'CIPM',
            buildStatus: 'Inaugurada',
        ),
    ];

    $repository = new FindTechnicalNotebookByProcessGoogleSheetRepository;

    $technicalNotebook = $repository->findByProcess($technicalNotebooks, '001.7313.2023.0006626-49');

    expect($technicalNotebook)->toBeInstanceOf(TechnicalNotebookEntity::class)
        ->and($technicalNotebook->process)->toBe('001.7313.2023.0006626-49')
        ->and($technicalNotebook->municipality)->toBe('Acajutiba')
        ->and($technicalNotebook->force)->toBe('PC')
        ->and($technicalNotebook->claim)->toBe('Delegacia')
        ->and($technicalNotebook->buildStatus)->toBe('Em andamento')
        ->and($technicalNotebook->estimatedValue)->toBe(1539740.33);
});

it('matches technical notebook municipalities with accent case and small typo variations', function (string $municipality) {
    $technicalNotebooks = [
        technicalNotebookGatewayEntity(municipality: 'Andaraí'),
        technicalNotebookGatewayEntity(municipality: 'Catu'),
    ];

    $results = (new FindTechnicalNotebookByMunicipalityGoogleSheetRepository)->findByMunicipality($technicalNotebooks, $municipality);

    expect($results)
        ->toHaveCount(1)
        ->and($results[0]->municipality)->toBe('Andaraí');
})->with([
    'uppercase without accent' => 'ANDARAI',
    'uppercase with accent' => 'ANDARAÍ',
    'title case with accent' => 'Andaraí',
    'lowercase without accent' => 'andarai',
    'minor typo with accent' => 'andarí',
    'title case without accent' => 'Andarai',
]);

function technicalNotebookGatewayEntity(
    string $municipality = 'Acajutiba',
    ?string $process = null,
    ?string $force = 'PC',
    ?string $claim = 'Delegacia',
    ?string $buildStatus = 'Em andamento',
): TechnicalNotebookEntity {
    return new TechnicalNotebookEntity(
        item: 1,
        stage: 'Planejamento',
        municipality: $municipality,
        process: $process,
        force: $force,
        claim: $claim,
        typology: '1B',
        typologyObservation: 'Padrão',
        estimatedValue: 1539740.33,
        inspection: 'Vistoria realizada',
        seiReport: '89122036',
        landStatus: 'Terreno doado',
        landRegularization: 'Regular',
        soilStudy: 'Solicitado',
        environmental: 'Dispensado',
        inspectionComment: 'Sem pendências',
        claimStage: 'Aprovado',
        biddingSei: 'SEI-123',
        contract: 'Contrato 123',
        fiplanInstrument: 'Instrumento 456',
        buildStatus: $buildStatus,
        inaugurationDate: null,
    );
}
