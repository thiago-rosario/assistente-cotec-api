<?php

declare(strict_types=1);

namespace App\Core\Conversation\Exception;

use App\Core\Conversation\Enum\CodeExceptionEnum;
use RuntimeException;
use Throwable;

class WhatsapppMessageException extends RuntimeException
{
    public function __construct(
        string $message = 'Erro ao ler mensagem do whatsapp',
        int $code = CodeExceptionEnum::WhatsappMessageError->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
