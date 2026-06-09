<?php

use App\Core\Application\DTO\SearchLandSurveyInputDTO;
use App\Core\Application\Usecase\SearchLandSurveyUsecase;
use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Domain\Repository\LandSurveyRepositoryInterface;

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

            return [landSurveyUsecaseEntity(municipality: $municipality)];
        }

        public function findByProcess(string $process): ?LandSurveyEntity
        {
            $this->calledMethod = __FUNCTION__;

            return landSurveyUsecaseEntity(process: $process);
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
                landSurveyUsecaseEntity(process: '030.2709.2022.0197573-43'),
                landSurveyUsecaseEntity(process: '031.2709.2022.0197573-43'),
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

function landSurveyUsecaseEntity(
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
