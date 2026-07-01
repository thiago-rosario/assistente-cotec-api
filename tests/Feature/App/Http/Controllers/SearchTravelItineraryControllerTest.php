<?php

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Domain\Repository\TravelItineraryRepositoryInterface;

beforeEach(function () {
    app()->bind(TravelItineraryRepositoryInterface::class, fn (): TravelItineraryRepositoryInterface => new class implements TravelItineraryRepositoryInterface
    {
        public function all(): array
        {
            return [];
        }

        public function search(string $term): array
        {
            return [];
        }

        public function findByMunicipality(string $municipality): array
        {
            return [];
        }

        public function findByProcess(string $process): ?TravelItineraryEntity
        {
            return searchTravelItineraryControllerEntity(process: $process);
        }

        public function findByForce(string $force): array
        {
            return [];
        }

        public function findByRegion(string $region): array
        {
            return [];
        }

        public function findByLandStatus(string $status): array
        {
            return [];
        }

        public function findByProgress(string $progress): array
        {
            return [];
        }

        public function findByRequester(string $requester): array
        {
            return [searchTravelItineraryControllerEntity(requester: $requester)];
        }
    });
});

it('searches travel itineraries by requester through the controller', function () {
    $this->getJson('/api/travel-itineraries/search?requester=Prefeitura%20de%20Catu')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'term' => null,
                'total' => 1,
                'data' => [
                    [
                        'municipality' => 'Catu',
                        'requester' => 'Prefeitura de Catu',
                        'route' => 'ROTA 01 - OK',
                    ],
                ],
            ],
        ]);
});

it('searches travel itineraries by sei process through the controller', function () {
    $this->getJson('/api/travel-itineraries/search?process=020.2301.2022.0007756-88')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'term' => null,
                'total' => 1,
                'data' => [
                    [
                        'municipality' => 'Catu',
                        'process' => '020.2301.2022.0007756-88',
                        'requester' => 'Prefeitura de Catu',
                    ],
                ],
            ],
        ]);
});

function searchTravelItineraryControllerEntity(
    string $municipality = 'Catu',
    ?string $process = null,
    string $requester = 'Prefeitura de Catu',
): TravelItineraryEntity {
    return new TravelItineraryEntity(
        municipality: $municipality,
        process: $process,
        region: 'Leste',
        unitClaim: 'Delegacia - Reforma 2ª COORPIN',
        force: 'PC',
        requester: $requester,
        landStatus: 'Aguardando visita técnica.',
        progress: 'Aguardando visita técnica.',
        focalPointContact: 'Narlison Borges',
        route: 'ROTA 01 - OK',
        mapLink: 'https://maps.app.goo.gl/Worg597Ru524Y9py9',
    );
}
