<?php

use App\Core\Infra\External\LogWhatsappMessageSender;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Log::spy();
});

it('logs the whatsapp message that would be sent externally', function () {
    (new LogWhatsappMessageSender)->send(
        phone: '5571999999999',
        message: 'Resposta gerada pelo assistente',
        externalId: 'message-001',
    );

    Log::shouldHaveReceived('info')
        ->with('whatsapp_message_send_simulated', Mockery::on(
            fn (array $context): bool => $context['phone'] === '5571999999999'
                && $context['message'] === 'Resposta gerada pelo assistente'
                && $context['external_id'] === 'message-001'
                && $context['message_length'] === mb_strlen('Resposta gerada pelo assistente')
                && $context['payload'] === [
                    'action' => 'EnviarMsg',
                    'message' => [
                        'telefone' => '5571999999999',
                        'msg' => 'Resposta gerada pelo assistente',
                        'id_msg' => 'message-001',
                    ],
                ],
        ))
        ->once();
});
