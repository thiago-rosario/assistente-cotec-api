<?php

declare(strict_types=1);

namespace App\Core\Infra\Mapper;

use App\Core\Application\Interfaces\Mapper\PythonMessagePayloadMapperInterface;

class PythonMessagePayloadMapper extends WhatsappWebhookPayloadMapper implements PythonMessagePayloadMapperInterface
{
    protected function defaultSource(): string
    {
        return 'python-whatsapp';
    }
}
