<?php

declare(strict_types=1);

namespace App\Core\Identity\Exception;

use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use RuntimeException;
use Throwable;

class PasswordConfirmationMismatchException extends RuntimeException
{
    public function __construct(
        string $message = 'A confirmação de senha não corresponde à senha informada.',
        int $code = IdentityCodeExceptionEnum::PasswordConfirmationMismatch->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
