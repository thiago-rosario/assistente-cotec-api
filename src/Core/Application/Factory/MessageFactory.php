<?php

declare(strict_types=1);

namespace App\Core\Application\Factory;

use App\Core\Application\DTO\ReceivedMessageDocumentInputDTO;
use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Factory\MessageFactoryInterface;
use App\Core\Domain\Entity\MessageDocumentEntity;
use App\Core\Domain\Entity\MessageEntity;

class MessageFactory implements MessageFactoryInterface
{
    public function fromReceivedInput(ReceivedMessageInputDTO $input): MessageEntity
    {
        return new MessageEntity(
            content: $input->message,
            phone: $input->phone,
            document: $this->document($input->document),
            externalId: $input->externalId,
            caption: $input->caption,
            receivedAt: $input->receivedAt,
            metadata: $input->metadata,
        );
    }

    private function document(?ReceivedMessageDocumentInputDTO $document): ?MessageDocumentEntity
    {
        if ($document === null) {
            return null;
        }

        return new MessageDocumentEntity(
            originalFileName: $document->originalFileName,
            mimeType: $document->mimeType,
            sizeBytes: $document->sizeBytes,
            contentBase64: $document->contentBase64,
            temporaryPath: $document->temporaryPath,
            metadata: $document->metadata,
        );
    }
}
