<?php

declare(strict_types=1);

namespace App\Core\Exception;

use App\Core\Enum\CodeExceptionEnum;
use InvalidArgumentException;
use Throwable;

class MessageNotContentException extends InvalidArgumentException
{
    public function __construct(
        string $message = 'Payload de mensagem recebido sem conteúdo.',
        int $code = CodeExceptionEnum::MessageNotContent->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
