<?php

use Revolution\Google\Sheets\Facades\Sheets;

it('searches only the configured google sheet requested by gid', function () {
    Sheets::shouldReceive('spreadsheet')
        ->once()
        ->with('1pcjdC19nNJAPKIYCirgwIBZIJsBrcFuCTpDEOUbpPOw')
        ->ordered()
        ->andReturnSelf();

    Sheets::shouldReceive('sheet')
        ->once()
        ->with("'CADERNO TÉCNICO'")
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
            ['POLO', '', ''],
            ['municipio', 'unidade', 'status'],
            ['Salvádor', 'Unidade X', 'Em andamento'],
            ['Feira de Santana', 'Unidade Y', 'Concluído'],
            ['Lauro de Freitas', 'Unidade Salvador Norte', 'Pendente'],
        ]));

    $this->getJson('/api/google-sheets/2106669123/search?q=salvador')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'spreadsheet_id' => '1pcjdC19nNJAPKIYCirgwIBZIJsBrcFuCTpDEOUbpPOw',
                'sheet_id' => 2106669123,
                'sheet' => 'CADERNO TÉCNICO',
                'search' => 'salvador',
                'total' => 2,
                'data' => [
                    [
                        'municipio' => 'Salvádor',
                        'unidade' => 'Unidade X',
                        'status' => 'Em andamento',
                    ],
                    [
                        'municipio' => 'Lauro de Freitas',
                        'unidade' => 'Unidade Salvador Norte',
                        'status' => 'Pendente',
                    ],
                ],
            ],
        ]);
});

it('returns a standardized error when the requested sheet is not configured', function () {
    Sheets::shouldReceive('spreadsheet')->never();

    $this->getJson('/api/google-sheets/999999/search?q=salvador')
        ->assertBadRequest()
        ->assertJson([
            'status' => 'error',
            'message' => 'A aba informada não está configurada para consulta.',
            'code' => 1007,
            'data' => null,
        ]);
});

it('requires the q query parameter for google sheet searches', function () {
    Sheets::shouldReceive('spreadsheet')->never();

    $this->getJson('/api/google-sheets/2106669123/search')
        ->assertUnprocessable()
        ->assertJson([
            'status' => 'fail',
            'data' => [
                'q' => ['O parâmetro q é obrigatório para busca.'],
            ],
        ]);
});

it('returns a standardized error when google sheet search reading fails', function () {
    Sheets::shouldReceive('spreadsheet')
        ->once()
        ->with('1pcjdC19nNJAPKIYCirgwIBZIJsBrcFuCTpDEOUbpPOw')
        ->andReturnSelf();

    Sheets::shouldReceive('sheet')
        ->once()
        ->with("'Reformas'")
        ->andReturnSelf();

    Sheets::shouldReceive('range')
        ->once()
        ->with('A:ZZ')
        ->andReturnSelf();

    Sheets::shouldReceive('get')
        ->once()
        ->andThrow(new RuntimeException('Google API unavailable'));

    $this->getJson('/api/google-sheets/1964615295/search?q=reforma')
        ->assertInternalServerError()
        ->assertJson([
            'status' => 'error',
            'message' => 'Falha ao ler os dados da planilha Google.',
            'code' => 1002,
            'data' => null,
        ]);
});
