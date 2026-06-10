<?php

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Domain\Repository\LandSurveyRepositoryInterface;
use Revolution\Google\Sheets\Facades\Sheets;

it('searches land surveys by sei process through the controller', function () {
    app()->bind(LandSurveyRepositoryInterface::class, fn (): LandSurveyRepositoryInterface => new class implements LandSurveyRepositoryInterface
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

        public function findByProcess(string $process): ?LandSurveyEntity
        {
            return searchLandSurveyControllerEntity(process: $process);
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

    $this->getJson('/api/land-surveys/search?process=030.2709.2022.0197573-43')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'term' => null,
                'total' => 1,
                'data' => [
                    [
                        'municipality' => 'Alcobaça',
                        'process' => '030.2709.2022.0197573-43',
                        'region' => 'Sul',
                    ],
                ],
            ],
        ]);
});

it('searches land surveys by sei process from the backup sheet real header row', function () {
    Sheets::shouldReceive('spreadsheet')->once()->andReturnSelf();
    Sheets::shouldReceive('sheet')->once()->with('BACKUP')->andReturnSelf();
    Sheets::shouldReceive('range')->once()->with('A:ZZ')->andReturnSelf();
    Sheets::shouldReceive('get')->once()->andReturn(collect([
        ['', 'Terreno vistoriado'],
        ['Resumo'],
        ['MUNICÍPIO', 'PROCESSO SEI', 'REGIÃO (RISP 2023)', 'PLEITO UNIDADE TAMANHO', 'FORÇA'],
        ['Camaçari', '001.7313.2023.0004933-59', 'RMS', 'Complexo Policial', 'PC/PM'],
    ]));

    $this->getJson('/api/land-surveys/search?process=001.7313.2023.0004933-59')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'term' => null,
                'total' => 1,
                'data' => [
                    [
                        'municipality' => 'Camaçari',
                        'process' => '001.7313.2023.0004933-59',
                        'region' => 'RMS',
                    ],
                ],
            ],
        ]);
});

function searchLandSurveyControllerEntity(
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
        municipalityFocalPointContact: 'Prefeito Givaldo Muniz',
        militaryPoliceFocalPointContact: 'Maj Marion',
        civilPoliceFocalPointContact: null,
        documentationLink: 'https://example.com/documentacao',
        updatedAt: new DateTimeImmutable('2026-05-01'),
        observations: 'Doado',
        requestedAt: new DateTimeImmutable('2019-03-20'),
    );
}
