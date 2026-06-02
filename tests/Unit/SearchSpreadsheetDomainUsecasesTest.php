<?php

use App\Core\Application\DTO\SearchConstructionDemandInputDTO;
use App\Core\Application\DTO\SearchLandSurveyInputDTO;
use App\Core\Application\DTO\SearchTravelItineraryInputDTO;
use App\Core\Application\Usecase\SearchConstructionDemandUsecase;
use App\Core\Application\Usecase\SearchLandSurveyUsecase;
use App\Core\Application\Usecase\SearchTravelItineraryUsecase;
use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Domain\Repository\ConstructionDemandRepositoryInterface;
use App\Core\Domain\Repository\LandSurveyRepositoryInterface;
use App\Core\Domain\Repository\TravelItineraryRepositoryInterface;

it('searches construction demands by the first filled filter and maps results', function () {
    $repository = new class implements ConstructionDemandRepositoryInterface
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

            return [constructionDemandEntity(municipality: $municipality)];
        }

        public function findByProcess(string $process): ?ConstructionDemandEntity
        {
            $this->calledMethod = __FUNCTION__;

            return constructionDemandEntity(process: $process);
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
    };

    $output = (new SearchConstructionDemandUsecase($repository))(
        new SearchConstructionDemandInputDTO(
            process: '001.7313.2023.0006626-49',
            municipality: 'Acajutiba',
            term: 'delegacia',
        ),
    );

    expect($repository->calledMethod)->toBe('findByProcess')
        ->and($output->term)->toBe('delegacia')
        ->and($output->total)->toBe(1)
        ->and($output->data[0]['process'])->toBe('001.7313.2023.0006626-49');
});

it('returns all construction demands when no filter or term is filled', function () {
    $repository = new class implements ConstructionDemandRepositoryInterface
    {
        public string $calledMethod = '';

        public function all(): array
        {
            $this->calledMethod = __FUNCTION__;

            return [
                constructionDemandEntity(process: '001.7313.2023.0006626-49'),
                constructionDemandEntity(process: '002.7313.2023.0006626-49'),
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

        public function findByProcess(string $process): ?ConstructionDemandEntity
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
    };

    $output = (new SearchConstructionDemandUsecase($repository))(new SearchConstructionDemandInputDTO);

    expect($repository->calledMethod)->toBe('all')
        ->and($output->term)->toBeNull()
        ->and($output->total)->toBe(2)
        ->and($output->data)->toHaveCount(2);
});

it('searches land surveys by the first filled filter and maps results', function () {
    $repository = new class implements LandSurveyRepositoryInterface
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

            return [landSurveyEntity(municipality: $municipality)];
        }

        public function findByProcess(string $process): ?LandSurveyEntity
        {
            $this->calledMethod = __FUNCTION__;

            return landSurveyEntity(process: $process);
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
    };

    $output = (new SearchLandSurveyUsecase($repository))(
        new SearchLandSurveyInputDTO(
            process: '030.2709.2022.0197573-43',
            municipality: 'Alcobaça',
            term: 'levantamento',
        ),
    );

    expect($repository->calledMethod)->toBe('findByProcess')
        ->and($output->term)->toBe('levantamento')
        ->and($output->total)->toBe(1)
        ->and($output->data[0]['process'])->toBe('030.2709.2022.0197573-43');
});

it('returns all land surveys when no filter or term is filled', function () {
    $repository = new class implements LandSurveyRepositoryInterface
    {
        public string $calledMethod = '';

        public function all(): array
        {
            $this->calledMethod = __FUNCTION__;

            return [
                landSurveyEntity(process: '030.2709.2022.0197573-43'),
                landSurveyEntity(process: '031.2709.2022.0197573-43'),
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

        public function findByProcess(string $process): ?LandSurveyEntity
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
    };

    $output = (new SearchLandSurveyUsecase($repository))(new SearchLandSurveyInputDTO);

    expect($repository->calledMethod)->toBe('all')
        ->and($output->term)->toBeNull()
        ->and($output->total)->toBe(2)
        ->and($output->data)->toHaveCount(2);
});

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

            return [travelItineraryEntity(municipality: $municipality)];
        }

        public function findByProcess(string $process): ?TravelItineraryEntity
        {
            $this->calledMethod = __FUNCTION__;

            return travelItineraryEntity(process: $process);
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
                travelItineraryEntity(process: '020.2301.2022.0007756-88'),
                travelItineraryEntity(process: '021.2301.2022.0007756-88'),
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

function constructionDemandEntity(
    string $municipality = 'Acajutiba',
    ?string $process = null,
): ConstructionDemandEntity {
    return new ConstructionDemandEntity(
        municipality: $municipality,
        force: 'PC',
        process: $process,
        unitClaim: 'Delegacia',
        requesterDescription: 'Prefeito Alexsandro Menezes (75) 99819-5058',
        landStatus: 'Terreno doado',
        progress: 'Terreno vistoriado e aprovado pela CEIRF',
        inspectionReport: '89122036',
        unitSizeClaim: '1B',
        region: 'Leste',
        requester: 'Prefeitura',
        soilSurveyAndTopography: 'solicitar',
    );
}

function landSurveyEntity(
    string $municipality = 'Alcobaça',
    ?string $process = null,
): LandSurveyEntity {
    return new LandSurveyEntity(
        municipality: $municipality,
        process: $process,
        region: 'Sul',
        unitSizeClaim: 'CIPM',
        force: 'PM',
        requester: 'Comando Região Sul',
        ownership: null,
        topography: 'Levantamento recebido',
        landStatus: 'Aguardando visita técnica',
        progress: 'Entrar em contato com prepostos da Prefeitura.',
        municipalityFocalPointContact: 'Prefeito Givaldo Muniz (73) 99926-2900',
        militaryPoliceFocalPointContact: 'Maj Marion (71)9959-6811',
        civilPoliceFocalPointContact: null,
        documentationLink: 'https://example.com/documentacao',
        updatedAt: new DateTimeImmutable('2026-05-01'),
        observations: 'Doado',
        requestedAt: new DateTimeImmutable('2019-03-20'),
    );
}

function travelItineraryEntity(
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
