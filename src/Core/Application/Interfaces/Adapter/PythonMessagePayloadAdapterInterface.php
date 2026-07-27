<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Adapter;

use App\Core\Application\DTO\ReceivedMessageInputDTO;

interface PythonMessagePayloadAdapterInterface extends WhatsappWebhookPayloadAdapterInterface
{
    /**
     * @return list<ReceivedMessageInputDTO>
     */
    public function fromPythonOutput(string $output): array;
}
