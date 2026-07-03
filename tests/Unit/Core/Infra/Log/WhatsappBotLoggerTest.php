<?php

use App\Core\Application\Interfaces\Log\WhatsappBotLoggerInterface;
use App\Core\Infra\Log\WhatsappBotLogger;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

it('resolves the whatsapp bot logger contract from the container', function () {
    expect(app(WhatsappBotLoggerInterface::class))->toBeInstanceOf(WhatsappBotLogger::class);
});

it('writes sanitized context to the whatsapp bot channel', function () {
    $channel = Mockery::mock();

    Log::shouldReceive('channel')
        ->once()
        ->with('whatsapp_bot')
        ->andReturn($channel);

    $channel->shouldReceive('info')
        ->once()
        ->with('message_detected', Mockery::on(function (array $context): bool {
            return $context['external_id'] === 'message-123'
                && mb_strlen($context['sender']) === 300
                && $context['filters']['municipality'] === 'Antas'
                && mb_strlen($context['filters']['raw']) === 300
                && $context['duration_ms'] === 0
                && ! array_key_exists('message', $context)
                && ! array_key_exists('raw_payload', $context);
        }));

    (new WhatsappBotLogger)->messageDetected([
        'external_id' => 'message-123',
        'sender' => str_repeat('a', 350),
        'source' => 'python-whatsapp',
        'filters' => [
            'municipality' => 'Antas',
            'raw' => str_repeat('b', 350),
        ],
        'duration_ms' => 0,
        'message' => 'texto recebido nao deve ir para o log',
        'raw_payload' => ['heavy' => true],
    ]);
});
