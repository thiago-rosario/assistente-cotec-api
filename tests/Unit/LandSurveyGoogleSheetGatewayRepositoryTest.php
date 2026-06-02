<?php

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Infra\Repository\Gateway\LandSurveyGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\SheetRepository\FindAllLandSurveyGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByForceGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByLandStatusGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByMunicipalityGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByProcessGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByProgressGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindLandSurveyByRegionGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\SearchLandSurveyGoogleSheetRepository;

it('delegates land survey gateway methods to their specific repositories', function () {
    $allRepository = new class extends FindAllLandSurveyGoogleSheetRepository
    {
        public bool $called = false;

        public function __construct() {}

        public function findAllSheet(): array
        {
            $this->called = true;

            return [landSurveyGatewayEntity(process: '030.2709.2022.0197573-43')];
        }
    };

    $searchRepository = new class extends SearchLandSurveyGoogleSheetRepository
    {
        public ?string $term = null;

        public array $receivedLandSurveys = [];

        public function search(array $landSurveys, string $term): array
        {
            $this->receivedLandSurveys = $landSurveys;
            $this->term = $term;

            return [landSurveyGatewayEntity(topography: $term)];
        }
    };

    $municipalityRepository = new class extends FindLandSurveyByMunicipalityGoogleSheetRepository
    {
        public ?string $municipality = null;

        public array $receivedLandSurveys = [];

        public function findByMunicipality(array $landSurveys, string $municipality): array
        {
            $this->receivedLandSurveys = $landSurveys;
            $this->municipality = $municipality;

            return [landSurveyGatewayEntity(municipality: $municipality)];
        }
    };

    $processRepository = new class extends FindLandSurveyByProcessGoogleSheetRepository
    {
        public ?string $process = null;

        public array $receivedLandSurveys = [];

        public function findByProcess(array $landSurveys, string $process): ?LandSurveyEntity
        {
            $this->receivedLandSurveys = $landSurveys;
            $this->process = $process;

            return landSurveyGatewayEntity(process: $process);
        }
    };

    $forceRepository = new class extends FindLandSurveyByForceGoogleSheetRepository
    {
        public ?string $force = null;

        public array $receivedLandSurveys = [];

        public function findByForce(array $landSurveys, string $force): array
        {
            $this->receivedLandSurveys = $landSurveys;
            $this->force = $force;

            return [landSurveyGatewayEntity(force: $force)];
        }
    };

    $regionRepository = new class extends FindLandSurveyByRegionGoogleSheetRepository
    {
        public ?string $region = null;

        public array $receivedLandSurveys = [];

        public function findByRegion(array $landSurveys, string $region): array
        {
            $this->receivedLandSurveys = $landSurveys;
            $this->region = $region;

            return [landSurveyGatewayEntity(region: $region)];
        }
    };

    $landStatusRepository = new class extends FindLandSurveyByLandStatusGoogleSheetRepository
    {
        public ?string $status = null;

        public array $receivedLandSurveys = [];

        public function findByLandStatus(array $landSurveys, string $status): array
        {
            $this->receivedLandSurveys = $landSurveys;
            $this->status = $status;

            return [landSurveyGatewayEntity(landStatus: $status)];
        }
    };

    $progressRepository = new class extends FindLandSurveyByProgressGoogleSheetRepository
    {
        public ?string $progress = null;

        public array $receivedLandSurveys = [];

        public function findByProgress(array $landSurveys, string $progress): array
        {
            $this->receivedLandSurveys = $landSurveys;
            $this->progress = $progress;

            return [landSurveyGatewayEntity(progress: $progress)];
        }
    };

    $gateway = new LandSurveyGoogleSheetGatewayRepository(
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
        ->and($gateway->search('levantamento'))->toHaveCount(1)
        ->and($searchRepository->term)->toBe('levantamento')
        ->and($searchRepository->receivedLandSurveys)->toHaveCount(1)
        ->and($gateway->findByMunicipality('Alcobaça'))->toHaveCount(1)
        ->and($municipalityRepository->municipality)->toBe('Alcobaça')
        ->and($municipalityRepository->receivedLandSurveys)->toHaveCount(1)
        ->and($gateway->findByProcess('030.2709.2022.0197573-43')?->process)->toBe('030.2709.2022.0197573-43')
        ->and($processRepository->process)->toBe('030.2709.2022.0197573-43')
        ->and($processRepository->receivedLandSurveys)->toHaveCount(1)
        ->and($gateway->findByForce('PM'))->toHaveCount(1)
        ->and($forceRepository->force)->toBe('PM')
        ->and($forceRepository->receivedLandSurveys)->toHaveCount(1)
        ->and($gateway->findByRegion('Extremo Sul'))->toHaveCount(1)
        ->and($regionRepository->region)->toBe('Extremo Sul')
        ->and($regionRepository->receivedLandSurveys)->toHaveCount(1)
        ->and($gateway->findByLandStatus('Aguardando visita técnica'))->toHaveCount(1)
        ->and($landStatusRepository->status)->toBe('Aguardando visita técnica')
        ->and($landStatusRepository->receivedLandSurveys)->toHaveCount(1)
        ->and($gateway->findByProgress('Terreno vistoriado'))->toHaveCount(1)
        ->and($progressRepository->progress)->toBe('Terreno vistoriado')
        ->and($progressRepository->receivedLandSurveys)->toHaveCount(1);
});

it('filters land surveys by normalized fields', function () {
    $landSurveys = [
        landSurveyGatewayEntity(
            municipality: 'Alcobaça',
            process: '030.2709.2022.0197573-43',
            region: 'Extremo Sul',
            force: 'PM',
            landStatus: 'Aguardando visita técnica',
            progress: 'Terreno vistoriado',
            topography: 'Levantamento recebido',
        ),
        landSurveyGatewayEntity(
            municipality: 'Catu',
            process: '031.2709.2022.0197573-43',
            region: 'Metropolitana',
            force: 'PC',
            landStatus: 'Terreno doado',
            progress: 'Concluído',
            topography: 'Pendente',
        ),
    ];

    expect((new FindLandSurveyByMunicipalityGoogleSheetRepository)->findByMunicipality($landSurveys, 'alcobaca'))->toHaveCount(1)
        ->and((new FindLandSurveyByForceGoogleSheetRepository)->findByForce($landSurveys, 'pc'))->toHaveCount(1)
        ->and((new FindLandSurveyByRegionGoogleSheetRepository)->findByRegion($landSurveys, 'extremo sul'))->toHaveCount(1)
        ->and((new FindLandSurveyByLandStatusGoogleSheetRepository)->findByLandStatus($landSurveys, 'aguardando visita tecnica'))->toHaveCount(1)
        ->and((new FindLandSurveyByProgressGoogleSheetRepository)->findByProgress($landSurveys, 'concluido'))->toHaveCount(1)
        ->and((new SearchLandSurveyGoogleSheetRepository)->search($landSurveys, 'levantamento'))->toHaveCount(1)
        ->and((new FindLandSurveyByProcessGoogleSheetRepository)->findByProcess($landSurveys, '030.2709.2022.0197573-43'))->toBeInstanceOf(LandSurveyEntity::class);
});

function landSurveyGatewayEntity(
    string $municipality = 'Alcobaça',
    ?string $process = null,
    ?string $region = 'Extremo Sul',
    ?string $force = 'PM',
    ?string $landStatus = 'Aguardando visita técnica',
    ?string $progress = 'Terreno vistoriado',
    ?string $topography = 'Levantamento recebido',
): LandSurveyEntity {
    return new LandSurveyEntity(
        municipality: $municipality,
        process: $process,
        region: $region,
        unitSizeClaim: '1B',
        force: $force,
        requester: 'COTEC',
        ownership: 'Município',
        topography: $topography,
        landStatus: $landStatus,
        progress: $progress,
        municipalityFocalPointContact: '(71) 99999-9999',
        militaryPoliceFocalPointContact: null,
        civilPoliceFocalPointContact: null,
        documentationLink: 'https://example.com/documentacao',
        updatedAt: null,
        observations: 'Sem pendências',
        requestedAt: null,
    );
}
