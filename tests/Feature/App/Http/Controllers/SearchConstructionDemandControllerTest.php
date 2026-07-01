<?php

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Domain\Repository\ConstructionDemandRepositoryInterface;

beforeEach(function () {
    app()->bind(ConstructionDemandRepositoryInterface::class, fn (): ConstructionDemandRepositoryInterface => new class implements ConstructionDemandRepositoryInterface
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

        public function findByProcess(string $process): ?ConstructionDemandEntity
        {
            return searchConstructionDemandControllerEntity(process: $process);
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
    });
});

it('searches construction demands by sei process through the controller', function () {
    $this->getJson('/api/construction-demands/search?process=001.7313.2023.0006626-49')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'term' => null,
                'total' => 1,
                'data' => [
                    [
                        'municipality' => 'Acajutiba',
                        'process' => '001.7313.2023.0006626-49',
                        'unitClaim' => 'Delegacia',
                    ],
                ],
            ],
        ]);
});

it('returns jsend validation errors for invalid construction demand search filters', function () {
    $this->getJson('/api/construction-demands/search?q[]=delegacia')
        ->assertUnprocessable()
        ->assertJson([
            'status' => 'fail',
            'data' => [
                'q' => ['The term field must be a string.'],
            ],
        ]);
});

function searchConstructionDemandControllerEntity(
    string $municipality = 'Acajutiba',
    ?string $process = null,
): ConstructionDemandEntity {
    return new ConstructionDemandEntity(
        municipality: $municipality,
        force: 'PC',
        process: $process,
        unitClaim: 'Delegacia',
        requesterDescription: 'Prefeito Alexsandro Menezes',
        landStatus: 'Terreno doado',
        progress: 'Terreno vistoriado',
        inspectionReport: '89122036',
        unitSizeClaim: '1B',
        region: 'Leste',
        requester: 'Prefeitura',
        soilSurveyAndTopography: 'solicitar',
    );
}
