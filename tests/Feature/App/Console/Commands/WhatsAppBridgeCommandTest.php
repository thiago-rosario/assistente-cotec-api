<?php

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Log\WhatsappBotLoggerInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Enum\WhatsappMessageIntentEnum;
use App\Core\Infra\External\PythonWhatsappMessageBridge;

it('logs the whatsapp bridge lifecycle while processing and replying to a message', function () {
    $message = new ReceivedMessageInputDTO(
        message: 'Buscar Antas',
        phone: '+5571999999999',
        senderName: 'Thiago',
        receivedAt: '19:30',
        source: 'python-whatsapp',
        externalId: 'message-123',
    );
    $reply = 'Encontrei 1 registro para o municipio ANTAS.';
    $sentReply = null;

    $logger = Mockery::mock(WhatsappBotLoggerInterface::class);
    $this->app->instance(WhatsappBotLoggerInterface::class, $logger);

    $logger->shouldReceive('botStarted')
        ->once()
        ->with(Mockery::subset(['source' => 'python-whatsapp']));

    $logger->shouldReceive('messageDetected')
        ->once()
        ->with(Mockery::subset([
            'external_id' => 'message-123',
            'sender' => 'Thiago',
            'source' => 'python-whatsapp',
        ]));

    $logger->shouldReceive('messageProcessingStarted')
        ->once()
        ->with(Mockery::subset([
            'external_id' => 'message-123',
            'sender' => 'Thiago',
            'source' => 'python-whatsapp',
        ]));

    $logger->shouldReceive('messageInterpreted')
        ->once()
        ->with(Mockery::on(fn (array $context): bool => $context['intent'] === WhatsappMessageIntentEnum::SEARCH_TECHNICAL_NOTEBOOK->value
            && $context['filters'] === ['municipality' => 'Antas']
            && $context['result_total'] === 1
            && $context['reply_length'] === mb_strlen($reply)
            && array_key_exists('duration_ms', $context)));

    $logger->shouldReceive('searchFinished')
        ->once()
        ->with(Mockery::on(fn (array $context): bool => $context['intent'] === WhatsappMessageIntentEnum::SEARCH_TECHNICAL_NOTEBOOK->value
            && $context['result_total'] === 1));

    $logger->shouldReceive('replySent')
        ->once()
        ->with(Mockery::on(fn (array $context): bool => $context['external_id'] === 'message-123'
            && $context['total_duration_ms'] >= 0));

    $processWhatsappMessage = Mockery::mock(ProcessWhatsappMessageUsecaseInterface::class);
    $this->app->instance(ProcessWhatsappMessageUsecaseInterface::class, $processWhatsappMessage);
    $processWhatsappMessage->shouldReceive('__invoke')
        ->once()
        ->with($message)
        ->andReturn([
            'reply' => $reply,
            'intent' => WhatsappMessageIntentEnum::SEARCH_TECHNICAL_NOTEBOOK->value,
            'total' => 1,
            'data' => [['municipality' => 'Antas']],
            'filters' => ['municipality' => 'Antas'],
        ]);

    $bridge = Mockery::mock(PythonWhatsappMessageBridge::class);
    $this->app->instance(PythonWhatsappMessageBridge::class, $bridge);
    $bridge->shouldReceive('stream')
        ->once()
        ->andReturnUsing(function (callable $handleMessage) use ($message, &$sentReply): int {
            $handleMessage($message, function (string $reply) use (&$sentReply): void {
                $sentReply = $reply;
            });

            return 0;
        });

    $this->artisan('whatsapp:bridge')
        ->assertExitCode(0);

    expect($sentReply)->toBe($reply);
});

it('logs skipped replies when the processed message has no response content', function () {
    $message = new ReceivedMessageInputDTO(
        message: 'Mensagem sem resposta',
        phone: '+5571888888888',
        externalId: 'message-456',
    );
    $sentReply = null;

    $logger = Mockery::mock(WhatsappBotLoggerInterface::class);
    $this->app->instance(WhatsappBotLoggerInterface::class, $logger);

    $logger->shouldReceive('botStarted')->once();
    $logger->shouldReceive('messageDetected')->once();
    $logger->shouldReceive('messageProcessingStarted')->once();
    $logger->shouldReceive('messageInterpreted')->once();
    $logger->shouldReceive('searchFinished')->never();
    $logger->shouldReceive('replySent')->never();
    $logger->shouldReceive('replySkipped')
        ->once()
        ->with(Mockery::on(fn (array $context): bool => $context['external_id'] === 'message-456'
            && $context['reason'] === 'empty_reply'
            && $context['total_duration_ms'] >= 0));

    $processWhatsappMessage = Mockery::mock(ProcessWhatsappMessageUsecaseInterface::class);
    $this->app->instance(ProcessWhatsappMessageUsecaseInterface::class, $processWhatsappMessage);
    $processWhatsappMessage->shouldReceive('__invoke')
        ->once()
        ->with($message)
        ->andReturn([
            'reply' => '',
            'intent' => 'unknown',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $bridge = Mockery::mock(PythonWhatsappMessageBridge::class);
    $this->app->instance(PythonWhatsappMessageBridge::class, $bridge);
    $bridge->shouldReceive('stream')
        ->once()
        ->andReturnUsing(function (callable $handleMessage) use ($message, &$sentReply): int {
            $handleMessage($message, function (string $reply) use (&$sentReply): void {
                $sentReply = $reply;
            });

            return 0;
        });

    $this->artisan('whatsapp:bridge')
        ->assertExitCode(0);

    expect($sentReply)->toBeNull();
});

it('logs a critical event when the python bridge exits with an error code', function () {
    $logger = Mockery::mock(WhatsappBotLoggerInterface::class);
    $this->app->instance(WhatsappBotLoggerInterface::class, $logger);

    $logger->shouldReceive('botStarted')->once();
    $logger->shouldReceive('botCritical')
        ->once()
        ->with(Mockery::type(RuntimeException::class), Mockery::subset([
            'source' => 'python-whatsapp',
            'reason' => 'bridge_exit_code',
        ]));

    $bridge = Mockery::mock(PythonWhatsappMessageBridge::class);
    $this->app->instance(PythonWhatsappMessageBridge::class, $bridge);
    $bridge->shouldReceive('stream')
        ->once()
        ->andReturn(2);

    $this->artisan('whatsapp:bridge')
        ->assertExitCode(2);
});
