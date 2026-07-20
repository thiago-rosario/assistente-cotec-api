<?php

declare(strict_types=1);

namespace App\Core\Identity\Exception;

use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use RuntimeException;
use Throwable;

class InvalidAuthorizationExpirationException extends RuntimeException
{
    public function __construct(
        string $message = 'A expiração da autorização temporária deve ser posterior à emissão.',
        int $code = IdentityCodeExceptionEnum::InvalidAuthorizationExpiration->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
