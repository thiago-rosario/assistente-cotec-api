<?php

use App\Core\Application\DTO\ReceivedMessageDocumentInputDTO;
use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Factory\MessageFactory;
use Tests\TestCase;

uses(TestCase::class);

it('preserves document metadata when creating a message entity', function () {
    $document = new ReceivedMessageDocumentInputDTO(
        originalFileName: 'relatorio-vistoria.pdf',
        mimeType: 'application/pdf',
        sizeBytes: 1024,
        caption: 'Relatório de vistoria técnica',
        contentBase64: 'JVBERi0xLjQK',
        metadata: [
            'type' => 'document',
        ],
    );

    $message = (new MessageFactory)->fromReceivedInput(new ReceivedMessageInputDTO(
        message: 'Confira o relatório',
        phone: '5571999999999',
        externalId: 'message-factory-001',
        document: $document,
        caption: $document->caption,
    ));

    expect($message->content())->toBe('Confira o relatório')
        ->and($message->document()?->originalFileName())->toBe('relatorio-vistoria.pdf')
        ->and($message->document()?->mimeType())->toBe('application/pdf')
        ->and($message->document()?->sizeBytes())->toBe(1024)
        ->and($message->document()?->contentBase64())->toBe('JVBERi0xLjQK')
        ->and($message->document()?->metadata())->toBe(['type' => 'document'])
        ->and($message->caption())->toBe('Relatório de vistoria técnica');
});
