<?php

declare(strict_types=1);

namespace App\Core\Identity\Exception;

use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use RuntimeException;
use Throwable;

class WhatsappNumberRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'O número do WhatsApp é obrigatório para a autorização temporária.',
        int $code = IdentityCodeExceptionEnum::WhatsappNumberRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
