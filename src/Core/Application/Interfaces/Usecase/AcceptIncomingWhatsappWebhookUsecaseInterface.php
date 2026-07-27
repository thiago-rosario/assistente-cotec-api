<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Usecase;

use App\Core\Application\DTO\ReceivedMessageInputDTO;

interface AcceptIncomingWhatsappWebhookUsecaseInterface
{
    /**
     * @return array{accepted: bool, external_id: string|null, duplicate: bool}
     */
    public function __invoke(ReceivedMessageInputDTO $input): array;
}
