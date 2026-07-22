<?php

declare(strict_types=1);

namespace App\Core\Identity\Exception;

use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use RuntimeException;
use Throwable;

class InvalidUserEmailException extends RuntimeException
{
    public function __construct(
        string $message = 'O e-mail do usuário é inválido.',
        int $code = IdentityCodeExceptionEnum::InvalidUserEmail->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
