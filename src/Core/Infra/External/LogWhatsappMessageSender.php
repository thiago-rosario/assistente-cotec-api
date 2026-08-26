<?php

declare(strict_types=1);

namespace App\Core\Infra\External;

use App\Core\Application\Interfaces\Service\WhatsappMessageSenderInterface;
use App\Core\Application\Support\WhatsappLogContext;
use Illuminate\Support\Facades\Log;

class LogWhatsappMessageSender implements WhatsappMessageSenderInterface
{
    public function send(
        string $phone,
        string $message,
        ?string $externalId = null,
    ): void {
        Log::info('whatsapp_reply_logged', [
            ...WhatsappLogContext::message($externalId, $phone, null),
            'id_msg' => $externalId,
            'reply' => $message,
            'reply_length' => mb_strlen($message),
            'delivery_mode' => 'log',
        ]);
    }
}
