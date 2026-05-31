<?php

use App\Http\Helper\ResponseJsend;

it('creates a success response with arrayable data', function () {
    $response = ResponseJsend::success(collect([
        'rows' => [
            ['nome' => 'Thiago'],
        ],
    ]));

    expect($response->toArray())->toBe([
        'status' => 'success',
        'data' => [
            'rows' => [
                ['nome' => 'Thiago'],
            ],
        ],
    ]);
});

it('creates an error response with message and code', function () {
    $response = ResponseJsend::error('Falha ao ler a planilha.', 500);

    expect($response->toArray())->toBe([
        'status' => 'error',
        'data' => null,
        'message' => 'Falha ao ler a planilha.',
        'code' => 500,
    ]);
});

it('requires a message for error responses', function () {
    expect(fn () => new ResponseJsend(status: ResponseJsend::STATUS_ERROR))
        ->toThrow(InvalidArgumentException::class, 'JSend error responses require a message.');
});
