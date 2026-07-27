<?php

declare(strict_types=1);

namespace App\Core\Infra\Adapter;

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
        ];
    }
}
