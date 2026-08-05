<?php

use App\Core\Application\DTO\ReceivedMessageDocumentInputDTO;
use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Factory\MessageFactory;
use App\Core\Application\Interfaces\Service\WhatsappMessageProcessorInterface;
use App\Core\Application\Usecase\ProcessWhatsappMessageUsecase;
use App\Core\Domain\Entity\MessageEntity;
use Tests\TestCase;

uses(TestCase::class);

it('passes the received document to the whatsapp message processor', function () {
    $processor = Mockery::mock(WhatsappMessageProcessorInterface::class);
    $processor->shouldReceive('process')
        ->once()
        ->with(Mockery::on(
            fn (MessageEntity $message): bool => $message->document()?->originalFileName() === 'relatorio-vistoria.pdf'
                && $message->document()?->mimeType() === 'application/pdf'
                && $message->document()?->sizeBytes() === 1024
                && $message->document()?->contentBase64() === 'JVBERi0xLjQK'
        ))
        ->andReturn([
            'reply' => '',
            'intent' => 'document_received',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ]);

    $result = (new ProcessWhatsappMessageUsecase(
        messages: new MessageFactory,
        processor: $processor,
    ))(new ReceivedMessageInputDTO(
        message: null,
        phone: '5571999999999',
        externalId: 'message-usecase-document-001',
        document: new ReceivedMessageDocumentInputDTO(
            originalFileName: 'relatorio-vistoria.pdf',
            mimeType: 'application/pdf',
            sizeBytes: 1024,
            contentBase64: 'JVBERi0xLjQK',
        ),
    ));

    expect($result['intent'])->toBe('document_received');
});
