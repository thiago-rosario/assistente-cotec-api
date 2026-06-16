<?php

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Adapter\PythonMessagePayloadAdapterInterface;
use App\Core\Infra\External\PythonWhatsappMessageBridge;

it('builds a python send message command for a processed whatsapp reply', function () {
    $bridge = new PythonWhatsappMessageBridge(
        adapter: Mockery::mock(PythonMessagePayloadAdapterInterface::class),
    );

    $command = $bridge->replyCommand(
        new ReceivedMessageInputDTO(
            message: 'Buscar processo 123',
            phone: '+5571999999999',
            senderName: 'Thiago',
            externalId: 'message-123',
        ),
        'Encontrei 1 registro no PAINEL DE OBRAS.',
    );

    expect(json_decode($command, true))->toBe([
        'type' => 'send_message',
        'payload' => [
            'customer_contact' => '+5571999999999',
            'content' => 'Encontrei 1 registro no PAINEL DE OBRAS.',
            'external_id' => 'message-123',
        ],
    ]);
});

it('uses the sender name as fallback when the received message has no phone', function () {
    $bridge = new PythonWhatsappMessageBridge(
        adapter: Mockery::mock(PythonMessagePayloadAdapterInterface::class),
    );

    $command = $bridge->replyCommand(
        new ReceivedMessageInputDTO(
            message: 'Buscar Salvador',
            senderName: 'Maria',
        ),
        'Não encontrei registros para essa consulta.',
    );

    expect(json_decode($command, true))->toBe([
        'type' => 'send_message',
        'payload' => [
            'customer_contact' => 'Maria',
            'content' => 'Não encontrei registros para essa consulta.',
            'external_id' => null,
        ],
    ]);
});
