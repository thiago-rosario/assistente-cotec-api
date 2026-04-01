<?php

declare(strict_types=1);

use App\Entities\TwilioEntity;
use Tests\TestCase;

uses(TestCase::class);

it('hydrates credentials from config when constructor values are missing', function () {
    config([
        'twilio.account_sid' => 'AC123',
        'twilio.auth_token' => 'token-abc',
        'twilio.whatsapp_from' => 'whatsapp:+5511999999999',
        'twilio.status_callback' => 'https://example.com/twilio/callback',
        'twilio.validate_signature' => true,
    ]);

    $entity = new TwilioEntity;

    expect($entity->getTwilioAccountSid())->toBe('AC123')
        ->and($entity->getTwilioAuthToken())->toBe('token-abc')
        ->and($entity->getTwilioWhatsappFrom())->toBe('whatsapp:+5511999999999')
        ->and($entity->statusCallback)->toBe('https://example.com/twilio/callback')
        ->and($entity->validateSignature)->toBeTrue();
});

it('throws when required credentials are missing', function () {
    config([
        'twilio.account_sid' => null,
        'twilio.auth_token' => null,
        'twilio.whatsapp_from' => null,
        'twilio.validate_signature' => false,
    ]);

    $entity = new TwilioEntity;

    expect(fn () => $entity->assertConfigured())
        ->toThrow(RuntimeException::class, 'TWILIO_ACCOUNT_SID');
});

it('formats whatsapp addresses with prefix only when needed', function () {
    $entity = new TwilioEntity('AC123', 'token-abc', 'whatsapp:+5511999999999');

    expect($entity->formatWhatsAppAddress('+5511988887777'))->toBe('whatsapp:+5511988887777')
        ->and($entity->formatWhatsAppAddress('whatsapp:+5511977776666'))->toBe('whatsapp:+5511977776666');
});
