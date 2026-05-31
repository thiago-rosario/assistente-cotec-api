<?php

use App\Core\Exception\GoogleSheetReadException;
use Illuminate\Support\Facades\Exceptions;
use Revolution\Google\Sheets\Facades\Sheets;

it('returns rows from the configured google sheets', function () {
    $sheetNames = [
        'DEMANDA DE CONSTRUÇÃO',
        'Caderno',
        'ROTAS',
        'Reformas',
        'TAMANHOS',
        'PESQUISA',
        'CADERNO TÉCNICO',
    ];

    foreach ($sheetNames as $sheetName) {
        Sheets::shouldReceive('spreadsheet')
            ->once()
            ->with('1pcjdC19nNJAPKIYCirgwIBZIJsBrcFuCTpDEOUbpPOw')
            ->ordered()
            ->andReturnSelf();

        Sheets::shouldReceive('sheet')
            ->once()
            ->with("'{$sheetName}'")
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
                ['nome', 'email'],
                [$sheetName, "{$sheetName}@example.com"],
            ]));
    }

    $this->getJson('/api/google-sheet')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'spreadsheet_id' => '1pcjdC19nNJAPKIYCirgwIBZIJsBrcFuCTpDEOUbpPOw',
                'total_sheets' => 7,
                'total_rows' => 7,
                'sheets' => [
                    [
                        'gid' => 615480757,
                        'sheet' => 'DEMANDA DE CONSTRUÇÃO',
                        'total' => 1,
                        'data' => [
                            [
                                'nome' => 'DEMANDA DE CONSTRUÇÃO',
                                'email' => 'DEMANDA DE CONSTRUÇÃO@example.com',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
});

it('returns diagnostic data when google sheet reading fails', function () {
    Exceptions::fake();

    Sheets::shouldReceive('spreadsheet')
        ->once()
        ->with('1pcjdC19nNJAPKIYCirgwIBZIJsBrcFuCTpDEOUbpPOw')
        ->andReturnSelf();

    Sheets::shouldReceive('sheet')
        ->once()
        ->with("'DEMANDA DE CONSTRUÇÃO'")
        ->andReturnSelf();

    Sheets::shouldReceive('range')
        ->once()
        ->with('A:ZZ')
        ->andReturnSelf();

    Sheets::shouldReceive('get')
        ->once()
        ->andThrow(new RuntimeException('Google API unavailable'));

    $this->getJson('/api/google-sheet')
        ->assertInternalServerError()
        ->assertJson([
            'status' => 'error',
            'message' => 'Falha ao ler os dados da planilha Google.',
            'code' => 1002,
            'data' => [
                'operation' => 'google_sheet_read',
                'spreadsheet_id' => '1pcjdC19nNJAPKIYCirgwIBZIJsBrcFuCTpDEOUbpPOw',
                'sheet' => [
                    'gid' => 615480757,
                    'name' => 'DEMANDA DE CONSTRUÇÃO',
                ],
                'exception' => RuntimeException::class,
                'reason' => 'Google API unavailable',
            ],
        ])
        ->assertJsonPath('data.location.file', __FILE__);

    Exceptions::assertReported(GoogleSheetReadException::class);
});
