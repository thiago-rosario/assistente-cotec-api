<?php

declare(strict_types=1);

namespace App\Core\Infra\Log;

use DateTimeZone;
use Illuminate\Log\Logger;

class CustomizeWhatsappBotLogger
{
    public function __invoke(Logger $logger): void
    {
        $timezone = config(
            'logging.channels.whatsapp_bot.timezone',
            config('app.timezone', 'America/Bahia')
        );

        $monolog = $logger->getLogger();

        if (method_exists($monolog, 'setTimezone')) {
            $monolog->setTimezone(new DateTimeZone($timezone));
        }
    }
}
