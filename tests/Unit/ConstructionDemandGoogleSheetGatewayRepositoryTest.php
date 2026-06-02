<?php

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Infra\Repository\Gateway\ConstructionDemandGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\SheetRepository\FindAllConstructionDemandGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByForceGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByLandStatusGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByMunicipalityGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByProcessGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByProgressGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindConstructionDemandByRegionGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\SearchConstructionDemandGoogleSheetRepository;

it('delegates construction demand gateway methods to their specific repositories', function () {
    $allRepository = new class extends FindAllConstructionDemandGoogleSheetRepository
    {
        public bool $called = false;

        public function __construct() {}

        public function findAllSheet(): array
        {
            $this->called = true;

            return [constructionDemandGatewayEntity(process: '001.7313.2023.0006626-49')];
        }
    };

    $searchRepository = new class extends SearchConstructionDemandGoogleSheetRepository
    {
        public ?string $term = null;

        public array $receivedConstructionDemands = [];

        public function search(array $constructionDemands, string $term): array
        {
            $this->receivedConstructionDemands = $constructionDemands;
            $this->term = $term;

            return [constructionDemandGatewayEntity(unitClaim: $term)];
        }
    };

    $municipalityRepository = new class extends FindConstructionDemandByMunicipalityGoogleSheetRepository
    {
        public ?string $municipality = null;

        public array $receivedConstructionDemands = [];

        public function findByMunicipality(array $constructionDemands, string $municipality): array
        {
            $this->receivedConstructionDemands = $constructionDemands;
            $this->municipality = $municipality;

            return [constructionDemandGatewayEntity(municipality: $municipality)];
        }
    };

    $processRepository = new class extends FindConstructionDemandByProcessGoogleSheetRepository
    {
        public ?string $process = null;

        public array $receivedConstructionDemands = [];

        public function findByProcess(array $constructionDemands, string $process): ?ConstructionDemandEntity
        {
            $this->receivedConstructionDemands = $constructionDemands;
            $this->process = $process;

            return constructionDemandGatewayEntity(process: $process);
        }
    };

    $forceRepository = new class extends FindConstructionDemandByForceGoogleSheetRepository
    {
        public ?string $force = null;

        public array $receivedConstructionDemands = [];

        public function findByForce(array $constructionDemands, string $force): array
        {
            $this->receivedConstructionDemands = $constructionDemands;
            $this->force = $force;

            return [constructionDemandGatewayEntity(force: $force)];
        }
    };

    $regionRepository = new class extends FindConstructionDemandByRegionGoogleSheetRepository
    {
        public ?string $region = null;

        public array $receivedConstructionDemands = [];

        public function findByRegion(array $constructionDemands, string $region): array
        {
            $this->receivedConstructionDemands = $constructionDemands;
            $this->region = $region;

            return [constructionDemandGatewayEntity(region: $region)];
        }
    };

    $landStatusRepository = new class extends FindConstructionDemandByLandStatusGoogleSheetRepository
    {
        public ?string $status = null;

        public array $receivedConstructionDemands = [];

        public function findByLandStatus(array $constructionDemands, string $status): array
        {
            $this->receivedConstructionDemands = $constructionDemands;
            $this->status = $status;

            return [constructionDemandGatewayEntity(landStatus: $status)];
        }
    };

    $progressRepository = new class extends FindConstructionDemandByProgressGoogleSheetRepository
    {
        public ?string $progress = null;

        public array $receivedConstructionDemands = [];

        public function findByProgress(array $constructionDemands, string $progress): array
        {
            $this->receivedConstructionDemands = $constructionDemands;
            $this->progress = $progress;

            return [constructionDemandGatewayEntity(progress: $progress)];
        }
    };

    $gateway = new ConstructionDemandGoogleSheetGatewayRepository(
        $allRepository,
        $searchRepository,
        $municipalityRepository,
        $processRepository,
        $forceRepository,
        $regionRepository,
        $landStatusRepository,
        $progressRepository,
    );

    expect($gateway->all())->toHaveCount(1)
        ->and($allRepository->called)->toBeTrue()
        ->and($gateway->search('delegacia'))->toHaveCount(1)
        ->and($searchRepository->term)->toBe('delegacia')
        ->and($searchRepository->receivedConstructionDemands)->toHaveCount(1)
        ->and($gateway->findByMunicipality('Acajutiba'))->toHaveCount(1)
        ->and($municipalityRepository->municipality)->toBe('Acajutiba')
        ->and($municipalityRepository->receivedConstructionDemands)->toHaveCount(1)
        ->and($gateway->findByProcess('001.7313.2023.0006626-49')?->process)->toBe('001.7313.2023.0006626-49')
        ->and($processRepository->process)->toBe('001.7313.2023.0006626-49')
        ->and($processRepository->receivedConstructionDemands)->toHaveCount(1)
        ->and($gateway->findByForce('PC'))->toHaveCount(1)
        ->and($forceRepository->force)->toBe('PC')
        ->and($forceRepository->receivedConstructionDemands)->toHaveCount(1)
        ->and($gateway->findByRegion('Litoral Norte'))->toHaveCount(1)
        ->and($regionRepository->region)->toBe('Litoral Norte')
        ->and($regionRepository->receivedConstructionDemands)->toHaveCount(1)
        ->and($gateway->findByLandStatus('Regular'))->toHaveCount(1)
        ->and($landStatusRepository->status)->toBe('Regular')
        ->and($landStatusRepository->receivedConstructionDemands)->toHaveCount(1)
        ->and($gateway->findByProgress('Em análise'))->toHaveCount(1)
        ->and($progressRepository->progress)->toBe('Em análise')
        ->and($progressRepository->receivedConstructionDemands)->toHaveCount(1);
});

it('filters construction demands by normalized fields', function () {
    $constructionDemands = [
        constructionDemandGatewayEntity(
            municipality: 'Acajutiba',
            process: '001.7313.2023.0006626-49',
            force: 'PC',
            unitClaim: 'Delegacia',
            landStatus: 'Regular',
            progress: 'Em análise',
            region: 'Litoral Norte',
        ),
        constructionDemandGatewayEntity(
            municipality: 'Catu',
            process: '020.2301.2022.0007756-88',
            force: 'PM',
            unitClaim: 'CIPM',
            landStatus: 'Pendente',
            progress: 'Concluído',
            region: 'Metropolitana',
        ),
    ];

    expect((new FindConstructionDemandByMunicipalityGoogleSheetRepository)->findByMunicipality($constructionDemands, 'acajutiba'))->toHaveCount(1)
        ->and((new FindConstructionDemandByForceGoogleSheetRepository)->findByForce($constructionDemands, 'pm'))->toHaveCount(1)
        ->and((new FindConstructionDemandByRegionGoogleSheetRepository)->findByRegion($constructionDemands, 'litoral norte'))->toHaveCount(1)
        ->and((new FindConstructionDemandByLandStatusGoogleSheetRepository)->findByLandStatus($constructionDemands, 'regular'))->toHaveCount(1)
        ->and((new FindConstructionDemandByProgressGoogleSheetRepository)->findByProgress($constructionDemands, 'concluido'))->toHaveCount(1)
        ->and((new SearchConstructionDemandGoogleSheetRepository)->search($constructionDemands, 'delegacia'))->toHaveCount(1)
        ->and((new FindConstructionDemandByProcessGoogleSheetRepository)->findByProcess($constructionDemands, '001.7313.2023.0006626-49'))->toBeInstanceOf(ConstructionDemandEntity::class);
});

function constructionDemandGatewayEntity(
    string $municipality = 'Acajutiba',
    ?string $process = null,
    ?string $force = 'PC',
    ?string $unitClaim = 'Delegacia',
    ?string $landStatus = 'Regular',
    ?string $progress = 'Em análise',
    ?string $region = 'Litoral Norte',
): ConstructionDemandEntity {
    return new ConstructionDemandEntity(
        municipality: $municipality,
        force: $force,
        process: $process,
        unitClaim: $unitClaim,
        requesterDescription: 'Construção de unidade',
        landStatus: $landStatus,
        progress: $progress,
        inspectionReport: '89122036',
        unitSizeClaim: '1B',
        region: $region,
        requester: 'COTEC',
        soilSurveyAndTopography: 'Solicitar',
    );
}
