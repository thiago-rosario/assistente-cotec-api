<?php

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Domain\Repository\ConstructionDemandRepositoryInterface;
use App\Core\Domain\Repository\LandSurveyRepositoryInterface;
use App\Core\Domain\Repository\TravelItineraryRepositoryInterface;
use Revolution\Google\Sheets\Facades\Sheets;

it('searches construction demands by sei process through the controller', function () {
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
            return resourceConstructionDemandEntity(process: $process);
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
            return resourceLandSurveyEntity(process: $process);
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

it('searches travel itineraries by requester through the controller', function () {
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
            return null;
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
            return [resourceTravelItineraryEntity(requester: $requester)];
        }
    });

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

it('searches travel itineraries by sei process from the rotas sheet real header row', function () {
    Sheets::shouldReceive('spreadsheet')->once()->andReturnSelf();
    Sheets::shouldReceive('sheet')->once()->with('ROTAS')->andReturnSelf();
    Sheets::shouldReceive('range')->once()->with('A:ZZ')->andReturnSelf();
    Sheets::shouldReceive('get')->once()->andReturn(collect([
        [],
        ['LINK 1: https://example.com'],
        ['LINK 2: https://example.com'],
        ['MUNICÍPIO', 'PROCESSO SEI', 'REGIÃO (RISP 2023)', 'PLEITO UNIDADE', 'FORÇA', 'REQUISITANTE'],
        ['Acajutiba', '001.7313.2023.0006626-49 020.2301.2022.0007756-88', 'Leste', 'Delegacia e Pelotão', 'PC/PM', 'Prefeitura de Acajutiba'],
    ]));

    $this->getJson('/api/travel-itineraries/search?process=020.2301.2022.0007756-88')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'term' => null,
                'total' => 1,
                'data' => [
                    [
                        'municipality' => 'Acajutiba',
                        'process' => '001.7313.2023.0006626-49 020.2301.2022.0007756-88',
                        'requester' => 'Prefeitura de Acajutiba',
                    ],
                ],
            ],
        ]);
});

it('returns jsend validation errors for invalid specific search filters', function () {
    $this->getJson('/api/construction-demands/search?q[]=delegacia')
        ->assertUnprocessable()
        ->assertJson([
            'status' => 'fail',
            'data' => [
                'q' => ['The term field must be a string.'],
            ],
        ]);
});

function resourceConstructionDemandEntity(
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

function resourceLandSurveyEntity(
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

function resourceTravelItineraryEntity(
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
