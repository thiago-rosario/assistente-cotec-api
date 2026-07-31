<?php

declare(strict_types=1);

namespace App\Core\Infra\External;

use App\Core\Application\Interfaces\Service\LogWhatsappMessageSenderInterface;
use App\Http\Helper\LogInfo;

class LogWhatsappMessageSender implements LogWhatsappMessageSenderInterface
{
    public function send(
        string $phone,
        string $message,
        ?string $externalId = null,
    ): void {
        LogInfo::whatsappMessageSendSimulated(
            phone: $phone,
            message: $message,
            externalId: $externalId,
        );
    }
}
