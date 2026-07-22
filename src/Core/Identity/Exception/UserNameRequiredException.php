<?php

declare(strict_types=1);

namespace App\Core\Identity\Exception;

use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use RuntimeException;
use Throwable;

class UserNameRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'O nome do usuário é obrigatório.',
        int $code = IdentityCodeExceptionEnum::UserNameRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
