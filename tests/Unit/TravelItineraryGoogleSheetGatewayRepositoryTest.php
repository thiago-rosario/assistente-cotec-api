<?php

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Repository\Gateway\TravelItineraryGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\SheetRepository\FindAllTravelItineraryGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByForceGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByLandStatusGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByMunicipalityGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByProcessGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByProgressGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByRegionGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\FindTravelItineraryByRequesterGoogleSheetRepository;
use App\Core\Infra\Repository\SheetRepository\SearchTravelItineraryGoogleSheetRepository;

it('delegates travel itinerary gateway methods to their specific repositories', function () {
    $allRepository = new class extends FindAllTravelItineraryGoogleSheetRepository
    {
        public bool $called = false;

        public function __construct() {}

        public function findAllSheet(): array
        {
            $this->called = true;

            return [travelItineraryGatewayEntity(process: '030.2709.2022.0197573-43')];
        }
    };

    $searchRepository = new class extends SearchTravelItineraryGoogleSheetRepository
    {
        public ?string $term = null;

        public array $receivedTravelItineraries = [];

        public function search(array $travelItineraries, string $term): array
        {
            $this->receivedTravelItineraries = $travelItineraries;
            $this->term = $term;

            return [travelItineraryGatewayEntity(route: $term)];
        }
    };

    $municipalityRepository = new class extends FindTravelItineraryByMunicipalityGoogleSheetRepository
    {
        public ?string $municipality = null;

        public array $receivedTravelItineraries = [];

        public function findByMunicipality(array $travelItineraries, string $municipality): array
        {
            $this->receivedTravelItineraries = $travelItineraries;
            $this->municipality = $municipality;

            return [travelItineraryGatewayEntity(municipality: $municipality)];
        }
    };

    $processRepository = new class extends FindTravelItineraryByProcessGoogleSheetRepository
    {
        public ?string $process = null;

        public array $receivedTravelItineraries = [];

        public function findByProcess(array $travelItineraries, string $process): ?TravelItineraryEntity
        {
            $this->receivedTravelItineraries = $travelItineraries;
            $this->process = $process;

            return travelItineraryGatewayEntity(process: $process);
        }
    };

    $forceRepository = new class extends FindTravelItineraryByForceGoogleSheetRepository
    {
        public ?string $force = null;

        public array $receivedTravelItineraries = [];

        public function findByForce(array $travelItineraries, string $force): array
        {
            $this->receivedTravelItineraries = $travelItineraries;
            $this->force = $force;

            return [travelItineraryGatewayEntity(force: $force)];
        }
    };

    $regionRepository = new class extends FindTravelItineraryByRegionGoogleSheetRepository
    {
        public ?string $region = null;

        public array $receivedTravelItineraries = [];

        public function findByRegion(array $travelItineraries, string $region): array
        {
            $this->receivedTravelItineraries = $travelItineraries;
            $this->region = $region;

            return [travelItineraryGatewayEntity(region: $region)];
        }
    };

    $landStatusRepository = new class extends FindTravelItineraryByLandStatusGoogleSheetRepository
    {
        public ?string $status = null;

        public array $receivedTravelItineraries = [];

        public function findByLandStatus(array $travelItineraries, string $status): array
        {
            $this->receivedTravelItineraries = $travelItineraries;
            $this->status = $status;

            return [travelItineraryGatewayEntity(landStatus: $status)];
        }
    };

    $progressRepository = new class extends FindTravelItineraryByProgressGoogleSheetRepository
    {
        public ?string $progress = null;

        public array $receivedTravelItineraries = [];

        public function findByProgress(array $travelItineraries, string $progress): array
        {
            $this->receivedTravelItineraries = $travelItineraries;
            $this->progress = $progress;

            return [travelItineraryGatewayEntity(progress: $progress)];
        }
    };

    $requesterRepository = new class extends FindTravelItineraryByRequesterGoogleSheetRepository
    {
        public ?string $requester = null;

        public array $receivedTravelItineraries = [];

        public function findByRequester(array $travelItineraries, string $requester): array
        {
            $this->receivedTravelItineraries = $travelItineraries;
            $this->requester = $requester;

            return [travelItineraryGatewayEntity(requester: $requester)];
        }
    };

    $gateway = new TravelItineraryGoogleSheetGatewayRepository(
        $allRepository,
        $searchRepository,
        $municipalityRepository,
        $processRepository,
        $forceRepository,
        $regionRepository,
        $landStatusRepository,
        $progressRepository,
        $requesterRepository,
    );

    expect($gateway->all())->toHaveCount(1)
        ->and($allRepository->called)->toBeTrue()
        ->and($gateway->search('rota'))->toHaveCount(1)
        ->and($searchRepository->term)->toBe('rota')
        ->and($searchRepository->receivedTravelItineraries)->toHaveCount(1)
        ->and($gateway->findByMunicipality('Alcobaça'))->toHaveCount(1)
        ->and($municipalityRepository->municipality)->toBe('Alcobaça')
        ->and($municipalityRepository->receivedTravelItineraries)->toHaveCount(1)
        ->and($gateway->findByProcess('030.2709.2022.0197573-43')?->process)->toBe('030.2709.2022.0197573-43')
        ->and($processRepository->process)->toBe('030.2709.2022.0197573-43')
        ->and($processRepository->receivedTravelItineraries)->toHaveCount(1)
        ->and($gateway->findByForce('PM'))->toHaveCount(1)
        ->and($forceRepository->force)->toBe('PM')
        ->and($forceRepository->receivedTravelItineraries)->toHaveCount(1)
        ->and($gateway->findByRegion('Extremo Sul'))->toHaveCount(1)
        ->and($regionRepository->region)->toBe('Extremo Sul')
        ->and($regionRepository->receivedTravelItineraries)->toHaveCount(1)
        ->and($gateway->findByLandStatus('Aguardando visita técnica'))->toHaveCount(1)
        ->and($landStatusRepository->status)->toBe('Aguardando visita técnica')
        ->and($landStatusRepository->receivedTravelItineraries)->toHaveCount(1)
        ->and($gateway->findByProgress('Terreno vistoriado'))->toHaveCount(1)
        ->and($progressRepository->progress)->toBe('Terreno vistoriado')
        ->and($progressRepository->receivedTravelItineraries)->toHaveCount(1)
        ->and($gateway->findByRequester('COTEC'))->toHaveCount(1)
        ->and($requesterRepository->requester)->toBe('COTEC')
        ->and($requesterRepository->receivedTravelItineraries)->toHaveCount(1);
});

it('filters travel itineraries by normalized fields', function () {
    $travelItineraries = [
        travelItineraryGatewayEntity(
            municipality: 'Alcobaça',
            process: '030.2709.2022.0197573-43 001.7313.2023.0006626-49',
            region: 'Extremo Sul',
            force: 'PM',
            requester: 'COTEC',
            landStatus: 'Aguardando visita técnica',
            progress: 'Terreno vistoriado',
            route: 'Salvador até Alcobaça',
        ),
        travelItineraryGatewayEntity(
            municipality: 'Catu',
            process: '031.2709.2022.0197573-43',
            region: 'Metropolitana',
            force: 'PC',
            requester: 'SSP',
            landStatus: 'Terreno doado',
            progress: 'Concluído',
            route: 'Salvador até Catu',
        ),
    ];

    expect((new FindTravelItineraryByMunicipalityGoogleSheetRepository)->findByMunicipality($travelItineraries, 'alcobaca'))->toHaveCount(1)
        ->and((new FindTravelItineraryByForceGoogleSheetRepository)->findByForce($travelItineraries, 'pc'))->toHaveCount(1)
        ->and((new FindTravelItineraryByRegionGoogleSheetRepository)->findByRegion($travelItineraries, 'extremo sul'))->toHaveCount(1)
        ->and((new FindTravelItineraryByLandStatusGoogleSheetRepository)->findByLandStatus($travelItineraries, 'aguardando visita tecnica'))->toHaveCount(1)
        ->and((new FindTravelItineraryByProgressGoogleSheetRepository)->findByProgress($travelItineraries, 'concluido'))->toHaveCount(1)
        ->and((new FindTravelItineraryByRequesterGoogleSheetRepository)->findByRequester($travelItineraries, 'ssp'))->toHaveCount(1)
        ->and((new SearchTravelItineraryGoogleSheetRepository)->search($travelItineraries, 'salvador'))->toHaveCount(2)
        ->and((new FindTravelItineraryByProcessGoogleSheetRepository)->findByProcess($travelItineraries, '001.7313.2023.0006626-49'))->toBeInstanceOf(TravelItineraryEntity::class);
});

function travelItineraryGatewayEntity(
    string $municipality = 'Alcobaça',
    ?string $process = null,
    ?string $region = 'Extremo Sul',
    ?string $unitClaim = 'Delegacia',
    ?string $force = 'PM',
    ?string $requester = 'COTEC',
    ?string $landStatus = 'Aguardando visita técnica',
    ?string $progress = 'Terreno vistoriado',
    ?string $focalPointContact = '(71) 99999-9999',
    ?string $route = 'Salvador até Alcobaça',
    ?string $mapLink = 'https://example.com/mapa',
): TravelItineraryEntity {
    return new TravelItineraryEntity(
        municipality: $municipality,
        process: $process,
        region: $region,
        unitClaim: $unitClaim,
        force: $force,
        requester: $requester,
        landStatus: $landStatus,
        progress: $progress,
        focalPointContact: $focalPointContact,
        route: $route,
        mapLink: $mapLink,
    );
}
