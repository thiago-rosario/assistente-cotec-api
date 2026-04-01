<?php

declare(strict_types=1);

use App\Entities\WhatsappEntity;

it('creates whatsapp entity from webhook payload', function () {
    $entity = WhatsappEntity::fromWebhookPayload([
        'MessageSid' => 'SM123',
        'From' => 'whatsapp:+5511912345678',
        'To' => 'whatsapp:+5511000000000',
        'Body' => 'Qual o horario de atendimento?',
    ]);

    expect($entity->getMessageSid())->toBe('SM123')
        ->and($entity->getFrom())->toBe('whatsapp:+5511912345678')
        ->and($entity->getTo())->toBe('whatsapp:+5511000000000')
        ->and($entity->getBody())->toBe('Qual o horario de atendimento?')
        ->and($entity->toBody())->toBe([
            'messageSid' => 'SM123',
            'from' => 'whatsapp:+5511912345678',
            'to' => 'whatsapp:+5511000000000',
            'body' => 'Qual o horario de atendimento?',
        ]);
});

it('supports setters for whatsapp payload data', function () {
    $entity = new WhatsappEntity;

    $entity
        ->setMessageSid('SM999')
        ->setFrom('+551188887777')
        ->setTo('+14155238886')
        ->setBody('mensagem livre');

    expect($entity->getMessageSid())->toBe('SM999')
        ->and($entity->getFrom())->toBe('whatsapp:+551188887777')
        ->and($entity->getTo())->toBe('whatsapp:+14155238886')
        ->and($entity->getBody())->toBe('mensagem livre')
        ->and($entity->toBody())->toBe([
            'messageSid' => 'SM999',
            'from' => 'whatsapp:+551188887777',
            'to' => 'whatsapp:+14155238886',
            'body' => 'mensagem livre',
        ])
        ->and($entity->toArray())->toBe($entity->toBody());
});
