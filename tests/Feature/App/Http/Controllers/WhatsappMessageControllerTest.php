<?php

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Adapter\PythonMessagePayloadAdapterInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use Mockery\MockInterface;

it('processes a whatsapp message and returns a jsend response', function () {
    $dto = new ReceivedMessageInputDTO(
        message: 'Procure Salvador',
        phone: '+5571999999999',
        senderName: 'Thiago',
    );

    $this->mock(PythonMessagePayloadAdapterInterface::class, function (MockInterface $mock) use ($dto) {
        $mock->shouldReceive('fromArray')
            ->once()
            ->with([
                'message' => 'Procure Salvador',
                'phone' => '+55 71 99999-9999',
                'sender_name' => 'Thiago',
            ])
            ->andReturn($dto);
    });

    $this->mock(ProcessWhatsappMessageUsecaseInterface::class, function (MockInterface $mock) use ($dto) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with($dto)
            ->andReturn([
                'reply' => 'Encontrei 1 registro no PAINEL DE OBRAS.',
                'intent' => 'search_technical_notebook',
                'total' => 1,
                'data' => [
                    [
                        'municipality' => 'Salvador',
                    ],
                ],
                'filters' => [
                    'municipality' => 'Salvador',
                ],
            ]);
    });

    $this->postJson('/api/whatsapp/messages', [
        'message' => 'Procure Salvador',
        'phone' => '+55 71 99999-9999',
        'sender_name' => 'Thiago',
    ])
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'reply' => 'Encontrei 1 registro no PAINEL DE OBRAS.',
                'intent' => 'search_technical_notebook',
                'total' => 1,
                'data' => [
                    [
                        'municipality' => 'Salvador',
                    ],
                ],
                'filters' => [
                    'municipality' => 'Salvador',
                ],
            ],
        ]);
});

it('accepts body as a whatsapp message alias', function () {
    $dto = new ReceivedMessageInputDTO(message: 'Buscar processo 123');

    $this->mock(PythonMessagePayloadAdapterInterface::class, function (MockInterface $mock) use ($dto) {
        $mock->shouldReceive('fromArray')
            ->once()
            ->with([
                'message' => 'Buscar processo 123',
            ])
            ->andReturn($dto);
    });

    $this->mock(ProcessWhatsappMessageUsecaseInterface::class, function (MockInterface $mock) use ($dto) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with($dto)
            ->andReturn([
                'reply' => 'Não encontrei registros para essa consulta.',
                'intent' => 'search_technical_notebook',
                'total' => 0,
                'data' => [],
                'filters' => [],
            ]);
    });

    $this->postJson('/api/whatsapp/messages', [
        'body' => 'Buscar processo 123',
    ])
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'reply' => 'Não encontrei registros para essa consulta.',
                'intent' => 'search_technical_notebook',
                'total' => 0,
                'data' => [],
                'filters' => [],
            ],
        ]);
});

it('returns jsend validation errors when the whatsapp message is missing', function () {
    $this->mock(PythonMessagePayloadAdapterInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('fromArray')->never();
    });

    $this->mock(ProcessWhatsappMessageUsecaseInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('__invoke')->never();
    });

    $this->postJson('/api/whatsapp/messages', [
        'phone' => '+5571999999999',
    ])
        ->assertUnprocessable()
        ->assertJson([
            'status' => 'fail',
            'data' => [
                'message' => ['The message field is required.'],
            ],
        ]);
});

it('returns a standardized error when whatsapp message processing fails', function () {
    $dto = new ReceivedMessageInputDTO(message: 'Buscar Salvador');

    $this->mock(PythonMessagePayloadAdapterInterface::class, function (MockInterface $mock) use ($dto) {
        $mock->shouldReceive('fromArray')
            ->once()
            ->andReturn($dto);
    });

    $this->mock(ProcessWhatsappMessageUsecaseInterface::class, function (MockInterface $mock) use ($dto) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with($dto)
            ->andThrow(new RuntimeException('Processing failed'));
    });

    $this->postJson('/api/whatsapp/messages', [
        'message' => 'Buscar Salvador',
    ])
        ->assertInternalServerError()
        ->assertJson([
            'status' => 'error',
            'message' => 'An unexpected error occurred',
            'code' => 500,
            'data' => null,
        ]);
});
