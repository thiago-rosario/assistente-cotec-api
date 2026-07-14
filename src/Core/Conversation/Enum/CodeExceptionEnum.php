<?php

declare(strict_types=1);

namespace App\Core\Conversation\Enum;

enum CodeExceptionEnum: int
{
    case MessageNotContent = 1000;
    case OpenAIEmptyResponse = 1001;
    case WhatsappMessageError = 1010;
}
