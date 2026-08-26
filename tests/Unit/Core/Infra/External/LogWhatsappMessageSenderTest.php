<?php

use App\Core\Infra\External\LogWhatsappMessageSender;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

it('writes the whatsapp reply to the log without calling an external service', function () {
    Log::spy();

    (new LogWhatsappMessageSender)->send(
        phone: '5571999999999',
        message: 'Olá! Eu sou o Assistente da COTEC.',
        externalId: 'log-message-001',
    );

    Log::shouldHaveReceived('info')
        ->with('whatsapp_reply_logged', Mockery::on(
            fn (array $context): bool => $context['external_id'] === 'log-message-001'
                && $context['phone'] === '5571*******99'
                && $context['reply'] === 'Olá! Eu sou o Assistente da COTEC.'
                && $context['delivery_mode'] === 'log'
                && $context['reply_length'] === mb_strlen('Olá! Eu sou o Assistente da COTEC.'),
        ))
        ->once();
});
