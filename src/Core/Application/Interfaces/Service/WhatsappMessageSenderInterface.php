<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Service;

interface WhatsappMessageSenderInterface
{
    public function send(
        string $phone,
        string $message,
        ?string $externalId = null,
    ): void;
}
