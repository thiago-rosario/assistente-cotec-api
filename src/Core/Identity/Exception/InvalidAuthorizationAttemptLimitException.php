<?php

declare(strict_types=1);

namespace App\Core\Identity\Exception;

use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use RuntimeException;
use Throwable;

class InvalidAuthorizationAttemptLimitException extends RuntimeException
{
    public function __construct(
        string $message = 'O limite de tentativas da autorização temporária é inválido.',
        int $code = IdentityCodeExceptionEnum::InvalidAuthorizationAttemptLimit->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
