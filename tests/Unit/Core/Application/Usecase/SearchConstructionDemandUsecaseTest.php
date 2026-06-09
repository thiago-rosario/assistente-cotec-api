<?php

use App\Core\Application\DTO\SearchConstructionDemandInputDTO;
use App\Core\Application\Usecase\SearchConstructionDemandUsecase;
use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Domain\Repository\ConstructionDemandRepositoryInterface;

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

            return [constructionDemandUsecaseEntity(municipality: $municipality)];
        }

        public function findByProcess(string $process): ?ConstructionDemandEntity
        {
            $this->calledMethod = __FUNCTION__;

            return constructionDemandUsecaseEntity(process: $process);
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
                constructionDemandUsecaseEntity(process: '001.7313.2023.0006626-49'),
                constructionDemandUsecaseEntity(process: '002.7313.2023.0006626-49'),
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

function constructionDemandUsecaseEntity(
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
