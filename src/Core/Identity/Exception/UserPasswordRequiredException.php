<?php

declare(strict_types=1);

namespace App\Core\Identity\Exception;

use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use RuntimeException;
use Throwable;

class UserPasswordRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'A senha do usuário é obrigatória.',
        int $code = IdentityCodeExceptionEnum::UserPasswordRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
