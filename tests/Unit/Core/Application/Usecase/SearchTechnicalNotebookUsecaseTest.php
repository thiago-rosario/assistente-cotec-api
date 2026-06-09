<?php

use App\Core\Application\DTO\SearchTechnicalNotebookInputDTO;
use App\Core\Application\Usecase\SearchTechnicalNotebookUsecase;
use App\Core\Domain\Entity\TechnicalNotebookEntity;
use App\Core\Domain\Repository\TechnicalNotebookRepositoryInterface;

it('searches technical notebooks by the first filled filter and maps results', function () {
    $repository = new class implements TechnicalNotebookRepositoryInterface
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

            return [technicalNotebookEntity(municipality: $municipality)];
        }

        public function findByProcess(string $process): ?TechnicalNotebookEntity
        {
            $this->calledMethod = __FUNCTION__;

            return technicalNotebookEntity(process: $process);
        }

        public function findByForce(string $force): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByBuildStatus(string $status): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }
    };

    $output = (new SearchTechnicalNotebookUsecase($repository))(
        new SearchTechnicalNotebookInputDTO(
            process: '001.7313.2023.0006626-49',
            municipality: 'Acajutiba',
            term: 'delegacia',
        ),
    );

    expect($repository->calledMethod)->toBe('findByProcess')
        ->and($output->term)->toBe('delegacia')
        ->and($output->total)->toBe(1)
        ->and($output->data)->toHaveCount(1)
        ->and($output->data[0]['process'])->toBe('001.7313.2023.0006626-49');
});

it('returns all technical notebooks when no filter or term is filled', function () {
    $repository = new class implements TechnicalNotebookRepositoryInterface
    {
        public string $calledMethod = '';

        public function all(): array
        {
            $this->calledMethod = __FUNCTION__;

            return [
                technicalNotebookEntity(process: '001.7313.2023.0006626-49'),
                technicalNotebookEntity(process: '002.7313.2023.0006626-49'),
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

        public function findByProcess(string $process): ?TechnicalNotebookEntity
        {
            $this->calledMethod = __FUNCTION__;

            return null;
        }

        public function findByForce(string $force): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }

        public function findByBuildStatus(string $status): array
        {
            $this->calledMethod = __FUNCTION__;

            return [];
        }
    };

    $output = (new SearchTechnicalNotebookUsecase($repository))(new SearchTechnicalNotebookInputDTO);

    expect($repository->calledMethod)->toBe('all')
        ->and($output->term)->toBeNull()
        ->and($output->total)->toBe(2)
        ->and($output->data)->toHaveCount(2);
});

function technicalNotebookEntity(
    string $municipality = 'Acajutiba',
    ?string $process = null,
): TechnicalNotebookEntity {
    return new TechnicalNotebookEntity(
        item: 1,
        stage: 'Planejamento',
        municipality: $municipality,
        process: $process,
        force: 'PC',
        claim: 'Delegacia',
        typology: '1B',
        typologyObservation: null,
        estimatedValue: 1539740.33,
        inspection: null,
        seiReport: null,
        landStatus: 'Terreno doado',
        landRegularization: null,
        soilStudy: null,
        environmental: null,
        inspectionComment: null,
        claimStage: null,
        biddingSei: null,
        contract: null,
        fiplanInstrument: null,
        buildStatus: 'Em andamento',
        inaugurationDate: null,
    );
}
