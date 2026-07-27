<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Usecase;

use App\Core\Application\DTO\ReceivedMessageInputDTO;

interface ProcessIncomingWhatsappWebhookUsecaseInterface
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ReceivedMessageInputDTO $input, ?int $attempt = null): array;
}
