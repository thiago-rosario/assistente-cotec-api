<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Adapter;

use App\Core\Application\DTO\ReceivedMessageInputDTO;

interface WhatsappWebhookPayloadAdapterInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function fromArray(array $payload): ReceivedMessageInputDTO;

    /**
     * @return array<string, mixed>
     */
    public function toArray(ReceivedMessageInputDTO $dto): array;
}
