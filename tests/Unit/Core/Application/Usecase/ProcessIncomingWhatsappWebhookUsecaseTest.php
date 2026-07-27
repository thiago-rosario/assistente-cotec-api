<?php

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Service\WhatsappMessageSenderInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Usecase\ProcessIncomingWhatsappWebhookUsecase;
use App\Jobs\ProcessIncomingWhatsappMessageJob;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Log::spy();
});

it('processes a whatsapp message and sends the generated reply', function () {
    $input = new ReceivedMessageInputDTO(
        message: 'Olá',
        phone: '5571999999999',
        source: 'editacodigo',
        externalId: 'message-001',
    );
    $process = Mockery::mock(ProcessWhatsappMessageUsecaseInterface::class);
    $sender = Mockery::mock(WhatsappMessageSenderInterface::class);

    $process->shouldReceive('__invoke')
        ->once()
        ->with($input)
        ->andReturn([
            'reply' => 'Resposta gerada pelo assistente',
            'intent' => 'greeting',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $sender->shouldReceive('send')
        ->once()
        ->with('5571999999999', 'Resposta gerada pelo assistente', 'message-001');

    $result = (new ProcessIncomingWhatsappWebhookUsecase($process, $sender))($input, 1);

    expect($result['reply'])->toBe('Resposta gerada pelo assistente');
});

it('does not call the sender when the reply is empty', function () {
    $input = new ReceivedMessageInputDTO(
        message: 'Mensagem sem resposta',
        phone: '5571999999999',
        source: 'editacodigo',
        externalId: 'message-empty',
    );
    $process = Mockery::mock(ProcessWhatsappMessageUsecaseInterface::class);
    $sender = Mockery::mock(WhatsappMessageSenderInterface::class);

    $process->shouldReceive('__invoke')
        ->once()
        ->with($input)
        ->andReturn([
            'reply' => '',
            'intent' => 'unknown',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $sender->shouldReceive('send')->never();

    (new ProcessIncomingWhatsappWebhookUsecase($process, $sender))($input, 1);
});

it('does not call the sender when the destination phone is missing', function () {
    $input = new ReceivedMessageInputDTO(
        message: 'Olá',
        source: 'editacodigo',
        externalId: 'message-missing-phone',
    );
    $process = Mockery::mock(ProcessWhatsappMessageUsecaseInterface::class);
    $sender = Mockery::mock(WhatsappMessageSenderInterface::class);

    $process->shouldReceive('__invoke')
        ->once()
        ->with($input)
        ->andReturn([
            'reply' => 'Resposta gerada pelo assistente',
            'intent' => 'greeting',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $sender->shouldReceive('send')->never();

    (new ProcessIncomingWhatsappWebhookUsecase($process, $sender))($input, 1);

    Log::shouldHaveReceived('warning')
        ->with('whatsapp_message_missing_phone', Mockery::on(
            fn (array $context): bool => $context['external_id'] === 'message-missing-phone'
                && $context['reply_length'] === mb_strlen('Resposta gerada pelo assistente')
        ))
        ->once();
});

it('lets the job execute the whatsapp processing flow with serializable payload data', function () {
    $process = Mockery::mock(ProcessWhatsappMessageUsecaseInterface::class);
    $sender = Mockery::mock(WhatsappMessageSenderInterface::class);
    $job = new ProcessIncomingWhatsappMessageJob([
        'message' => 'Buscar Salvador',
        'phone' => '5571999999999',
        'source' => 'editacodigo',
        'external_id' => 'job-001',
        'metadata' => [
            'source' => 'test',
        ],
    ]);

    $process->shouldReceive('__invoke')
        ->once()
        ->with(Mockery::on(
            fn (ReceivedMessageInputDTO $input): bool => $input->message === 'Buscar Salvador'
                && $input->phone === '5571999999999'
                && $input->source === 'editacodigo'
                && $input->externalId === 'job-001'
                && $input->metadata === ['source' => 'test']
        ))
        ->andReturn([
            'reply' => '',
            'intent' => 'search_technical_notebook',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $sender->shouldReceive('send')->never();

    $job->handle(new ProcessIncomingWhatsappWebhookUsecase($process, $sender));
});
