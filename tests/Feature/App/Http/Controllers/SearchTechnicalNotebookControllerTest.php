<?php

use Revolution\Google\Sheets\Facades\Sheets;

it('searches technical notebooks by municipality and returns complete row information', function () {
    Sheets::shouldReceive('spreadsheet')
        ->once()
        ->with('1pcjdC19nNJAPKIYCirgwIBZIJsBrcFuCTpDEOUbpPOw')
        ->ordered()
        ->andReturnSelf();

    Sheets::shouldReceive('sheet')
        ->once()
        ->with('CADERNO TÉCNICO')
        ->ordered()
        ->andReturnSelf();

    Sheets::shouldReceive('range')
        ->once()
        ->with('A:ZZ')
        ->ordered()
        ->andReturnSelf();

    Sheets::shouldReceive('get')
        ->once()
        ->ordered()
        ->andReturn(collect([
            ['POLO', '', '', '', ''],
            [
                'ITEM',
                'ETAPA',
                'MUNICIPIO',
                'PROCESSO',
                'FORÇA',
                'PLEITO',
                'TIPOLOGIA',
                'OBS. TIPOLOGIA',
                'VALOR ESTIMADO',
                'VISTORIA',
                'RELATÓRIO SEI',
                'STATUS DO TERRENO',
                'REGULARIZAÇÃO FUNDIÁRIA',
                'ESTUDO DE SOLO',
                'AMBIENTAL',
                'COMENTARIO DA FISCALIZAÇÃO',
                'ETAPA PLEITO',
                'SEI LICITAÇÃO',
                'CONTRATO',
                'INSTRUMENTO FIPLAN',
                'STATUS DE OBRA',
            ],
            [
                '1',
                'Planejamento',
                'Salvador',
                '001.7313.2023.0006626-49',
                'PC',
                'Delegacia',
                '1B',
                'Observação',
                '1.539.740,33',
                'Realizada',
                'SEI-123',
                'Terreno doado',
                'Regularizado',
                'Concluído',
                'Licenciado',
                'Sem pendências',
                'Análise',
                'SEI-LIC-123',
                'Contrato 123',
                'Fiplan 123',
                'Em andamento',
            ],
            [
                '2',
                'Planejamento',
                'Feira de Santana',
                '002.7313.2023.0006626-49',
                'PM',
                'Batalhão',
                '2B',
                '',
                '1000000',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Concluída',
            ],
        ]));

    $this->getJson('/api/technical-notebooks/search?municipality=Salvador')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'term' => null,
                'total' => 1,
                'data' => [
                    [
                        'item' => 1,
                        'stage' => 'Planejamento',
                        'municipality' => 'Salvador',
                        'process' => '001.7313.2023.0006626-49',
                        'force' => 'PC',
                        'claim' => 'Delegacia',
                        'typology' => '1B',
                        'typologyObservation' => 'Observação',
                        'estimatedValue' => 1539740.33,
                        'inspection' => 'Realizada',
                        'seiReport' => 'SEI-123',
                        'landStatus' => 'Terreno doado',
                        'landRegularization' => 'Regularizado',
                        'soilStudy' => 'Concluído',
                        'environmental' => 'Licenciado',
                        'inspectionComment' => 'Sem pendências',
                        'claimStage' => 'Análise',
                        'biddingSei' => 'SEI-LIC-123',
                        'contract' => 'Contrato 123',
                        'fiplanInstrument' => 'Fiplan 123',
                        'buildStatus' => 'Em andamento',
                        'inaugurationDate' => null,
                    ],
                ],
            ],
        ]);
});

it('uses q as the technical notebook search term when term is not provided', function () {
    Sheets::shouldReceive('spreadsheet')->once()->andReturnSelf();
    Sheets::shouldReceive('sheet')->once()->with('CADERNO TÉCNICO')->andReturnSelf();
    Sheets::shouldReceive('range')->once()->with('A:ZZ')->andReturnSelf();
    Sheets::shouldReceive('get')->once()->andReturn(collect([
        ['MUNICIPIO', 'PLEITO'],
        ['Salvador', 'Delegacia'],
        ['Feira de Santana', 'Batalhão'],
    ]));

    $this->getJson('/api/technical-notebooks/search?q=delegacia')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'term' => 'delegacia',
                'total' => 1,
                'data' => [
                    [
                        'municipality' => 'Salvador',
                        'claim' => 'Delegacia',
                    ],
                ],
            ],
        ]);
});

it('returns jsend validation errors for invalid technical notebook filters', function () {
    Sheets::shouldReceive('spreadsheet')->never();

    $this->getJson('/api/technical-notebooks/search?q[]=delegacia')
        ->assertUnprocessable()
        ->assertJson([
            'status' => 'fail',
            'data' => [
                'q' => ['The term field must be a string.'],
            ],
        ]);
});

it('returns a standardized error when technical notebook reading fails', function () {
    Sheets::shouldReceive('spreadsheet')->once()->andReturnSelf();
    Sheets::shouldReceive('sheet')->once()->with('CADERNO TÉCNICO')->andReturnSelf();
    Sheets::shouldReceive('range')->once()->with('A:ZZ')->andReturnSelf();
    Sheets::shouldReceive('get')->once()->andThrow(new RuntimeException('Google API unavailable'));

    $this->getJson('/api/technical-notebooks/search?force=PC')
        ->assertInternalServerError()
        ->assertJson([
            'status' => 'error',
            'message' => 'An unexpected error occurred',
            'code' => 500,
            'data' => null,
        ]);
});
