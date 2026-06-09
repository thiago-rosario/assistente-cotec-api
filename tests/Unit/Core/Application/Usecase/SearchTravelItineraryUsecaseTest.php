<?php

use App\Core\Application\DTO\SearchTravelItineraryInputDTO;
use App\Core\Application\Usecase\SearchTravelItineraryUsecase;
use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Domain\Repository\TravelItineraryRepositoryInterface;

it('searches travel itineraries by the first filled filter and maps results', function () {
    $repository = new class implements TravelItineraryRepositoryInterface
    {
        public string $calledMethod = '';

        public function all(): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function search(string $term): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByMunicipality(string $municipality): array
        {
            $this->calledMethod = __FUNCTION__;

            return [travelItineraryUsecaseEntity(municipality: $municipality)];
        }

        public function findByProcess(string $process): ?TravelItineraryEntity
        {
            $this->calledMethod = __FUNCTION__;

            return travelItineraryUsecaseEntity(process: $process);
        }

        public function findByForce(string $force): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByRegion(string $region): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByLandStatus(string $status): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByProgress(string $progress): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByRequester(string $requester): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }
    };

    $output = (new SearchTravelItineraryUsecase($repository))(
        new SearchTravelItineraryInputDTO(
            process: '020.2301.2022.0007756-88',
            municipality: 'Catu',
            term: 'rota',
        ),
    );

    expect($repository->calledMethod)->toBe('findByProcess')
        ->and($output->term)->toBe('rota')
        ->and($output->total)->toBe(1)
        ->and($output->data[0]['process'])->toBe('020.2301.2022.0007756-88');
});

it('returns all travel itineraries when no filter or term is filled', function () {
    $repository = new class implements TravelItineraryRepositoryInterface
    {
        public string $calledMethod = '';

        public function all(): array
        {
            $this->calledMethod = __FUNCTION__;

            return [
                travelItineraryUsecaseEntity(process: '020.2301.2022.0007756-88'),
                travelItineraryUsecaseEntity(process: '021.2301.2022.0007756-88'),
            ];
        }

        public function search(string $term): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByMunicipality(string $municipality): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByProcess(string $process): ?TravelItineraryEntity
        {
            $this->calledMethod = __FUNCTION__;

            return null;
        }

        public function findByForce(string $force): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByRegion(string $region): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByLandStatus(string $status): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByProgress(string $progress): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByRequester(string $requester): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }
    };

    $output = (new SearchTravelItineraryUsecase($repository))(new SearchTravelItineraryInputDTO);

    expect($repository->calledMethod)->toBe('all')
        ->and($output->term)->toBeNull()
        ->and($output->total)->toBe(2)
        ->and($output->data)->toHaveCount(2);
});

function travelItineraryUsecaseEntity(
    string $municipality = 'Catu',
    ?string $process = null,
): TravelItineraryEntity {
    return new TravelItineraryEntity(
        municipality: $municipality,
        process: $process,
        region: 'Leste',
        unitClaim: 'Delegacia - Reforma 2ª COORPIN',
        force: 'PC',
        requester: 'Prefeitura de Catu',
        landStatus: 'Aguardando visita técnica.',
        progress: 'Aguardando visita técnica.',
        focalPointContact: 'Narlison Borges (Prefeito) (71) 99681-7358',
        route: 'ROTA 01 - OK',
        mapLink: 'https://maps.app.goo.gl/Worg597Ru524Y9py9',
    );
}
