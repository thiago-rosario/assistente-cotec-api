<?php

declare(strict_types=1);

namespace App\Core\Identity\Exception;

use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use RuntimeException;
use Throwable;

class AuthorizationIdRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'O identificador da autorização temporária é obrigatório.',
        int $code = IdentityCodeExceptionEnum::AuthorizationIdRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
