<?php

declare(strict_types=1);

namespace App\Core\Infra\Adapter;

use App\Core\Application\DTO\ReceivedMessageDocumentInputDTO;
use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Adapter\WhatsappWebhookPayloadAdapterInterface;
use App\Core\Application\Interfaces\Mapper\WhatsappWebhookPayloadMapperInterface;
use App\Core\Domain\Resolver\PhoneNormalizerResolver;

class WhatsappWebhookPayloadAdapter implements WhatsappWebhookPayloadAdapterInterface
{
    public function __construct(
        private readonly WhatsappWebhookPayloadMapperInterface $mapper,
        private readonly PhoneNormalizerResolver $resolver,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function fromArray(array $payload): ReceivedMessageInputDTO
    {
        $mappedPayload = $this->mapper->map($payload);
        $phone = $this->resolver->normalize($mappedPayload['customer_contact']);

        return new ReceivedMessageInputDTO(
            message: $mappedPayload['message'],
            phone: $phone,
            senderName: $mappedPayload['sender_name'] ?? ($phone === null ? $mappedPayload['customer_contact'] : null),
            receivedAt: $mappedPayload['received_at'],
            source: $mappedPayload['source'],
            externalId: $mappedPayload['external_id'],
            metadata: $mappedPayload['metadata'],
            document: $this->document($mappedPayload['document']),
            caption: $mappedPayload['caption'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(ReceivedMessageInputDTO $dto): array
    {
        return [
            'message' => $dto->message,
            'phone' => $dto->phone,
            'sender_name' => $dto->senderName,
            'received_at' => $dto->receivedAt,
            'source' => $dto->source,
            'external_id' => $dto->externalId,
            'metadata' => $dto->metadata,
            'document' => $dto->document === null ? null : [
                'original_file_name' => $dto->document->originalFileName,
                'mime_type' => $dto->document->mimeType,
                'size_bytes' => $dto->document->sizeBytes,
                'caption' => $dto->document->caption,
                'content_base64' => $dto->document->contentBase64,
                'temporary_path' => $dto->document->temporaryPath,
                'metadata' => $dto->document->metadata,
            ],
            'caption' => $dto->caption,
        ];
    }

    /**
     * @param  array{
     *     original_file_name: string,
     *     mime_type: string,
     *     size_bytes: int,
     *     caption: string|null,
     *     content_base64: string|null,
     *     temporary_path: string|null,
     *     metadata: array<string, mixed>
     * }|null  $document
     */
    private function document(?array $document): ?ReceivedMessageDocumentInputDTO
    {
        if ($document === null) {
            return null;
        }

        return new ReceivedMessageDocumentInputDTO(
            originalFileName: $document['original_file_name'],
            mimeType: $document['mime_type'],
            sizeBytes: $document['size_bytes'],
            caption: $document['caption'],
            contentBase64: $document['content_base64'],
            temporaryPath: $document['temporary_path'],
            metadata: $document['metadata'],
        );
    }
}
