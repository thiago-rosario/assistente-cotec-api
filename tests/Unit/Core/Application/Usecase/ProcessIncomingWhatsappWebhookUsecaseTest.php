<?php

use App\Core\Application\DTO\ReceivedMessageDocumentInputDTO;
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

it('does not include document base64 in processing logs', function () {
    $base64 = 'JVBERi0xLjQK';
    $input = new ReceivedMessageInputDTO(
        message: null,
        phone: '5571999999999',
        source: 'editacodigo',
        externalId: 'message-log-001',
        document: new ReceivedMessageDocumentInputDTO(
            originalFileName: 'relatorio-vistoria.pdf',
            mimeType: 'application/pdf',
            sizeBytes: 1024,
            contentBase64: $base64,
        ),
    );
    $process = Mockery::mock(ProcessWhatsappMessageUsecaseInterface::class);
    $sender = Mockery::mock(WhatsappMessageSenderInterface::class);

    $process->shouldReceive('__invoke')
        ->once()
        ->with($input)
        ->andReturn([
            'reply' => '',
            'intent' => 'document_received',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $sender->shouldReceive('send')->never();

    (new ProcessIncomingWhatsappWebhookUsecase($process, $sender))($input, 1);

    Log::shouldHaveReceived('info')
        ->with('whatsapp_message_processing_started', Mockery::on(
            fn (array $context): bool => ! str_contains((string) json_encode($context), $base64)
        ))
        ->once();

    Log::shouldHaveReceived('info')
        ->with('whatsapp_message_processed', Mockery::on(
            fn (array $context): bool => ! str_contains((string) json_encode($context), $base64)
        ))
        ->once();
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

it('lets the job execute the whatsapp processing flow for text messages', function () {
    $process = Mockery::mock(ProcessWhatsappMessageUsecaseInterface::class);
    $sender = Mockery::mock(WhatsappMessageSenderInterface::class);
    $job = new ProcessIncomingWhatsappMessageJob([
        'message' => 'Buscar Salvador',
        'phone' => '5571999999999',
        'source' => 'editacodigo',
        'external_id' => 'job-text-001',
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
                && $input->externalId === 'job-text-001'
                && $input->document === null
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

it('lets the job preserve a document when rebuilding the whatsapp input', function () {
    $process = Mockery::mock(ProcessWhatsappMessageUsecaseInterface::class);
    $sender = Mockery::mock(WhatsappMessageSenderInterface::class);
    $base64 = 'JVBERi0xLjQK';
    $job = new ProcessIncomingWhatsappMessageJob([
        'message' => null,
        'phone' => '5571999999999',
        'source' => 'editacodigo',
        'external_id' => 'job-001',
        'caption' => 'Relatório de vistoria técnica',
        'document' => [
            'original_file_name' => 'relatorio-vistoria.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'caption' => 'Relatório de vistoria técnica',
            'content_base64' => $base64,
            'temporary_path' => null,
            'metadata' => [
                'type' => 'document',
            ],
        ],
        'metadata' => [
            'source' => 'test',
        ],
    ]);

    $job = unserialize(serialize($job));

    $process->shouldReceive('__invoke')
        ->once()
        ->with(Mockery::on(
            fn (ReceivedMessageInputDTO $input): bool => $input->message === null
                && $input->phone === '5571999999999'
                && $input->source === 'editacodigo'
                && $input->externalId === 'job-001'
                && $input->caption === 'Relatório de vistoria técnica'
                && $input->document?->originalFileName === 'relatorio-vistoria.pdf'
                && $input->document?->mimeType === 'application/pdf'
                && $input->document?->sizeBytes === 1024
                && $input->document?->contentBase64 === $base64
                && $input->document?->caption === 'Relatório de vistoria técnica'
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
