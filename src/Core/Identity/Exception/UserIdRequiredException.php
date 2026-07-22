<?php

declare(strict_types=1);

namespace App\Core\Identity\Exception;

use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use RuntimeException;
use Throwable;

class UserIdRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'O identificador do usuário é obrigatório.',
        int $code = IdentityCodeExceptionEnum::UserIdRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
