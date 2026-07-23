<?php

declare(strict_types=1);

namespace App\Core\Identity\Exception;

use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use RuntimeException;
use Throwable;

class IdentityApplicationException extends RuntimeException
{
    public function __construct(
        public readonly IdentityCodeExceptionEnum $identityCode,
        ?string $message = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message ?? self::defaultMessage($identityCode),
            code: $identityCode->value,
            previous: $previous,
        );
    }

    private static function defaultMessage(IdentityCodeExceptionEnum $identityCode): string
    {
        return match ($identityCode) {
            IdentityCodeExceptionEnum::TemporaryAuthorizationNotFound => 'Temporary authorization was not found.',
            IdentityCodeExceptionEnum::InvalidTemporaryAuthorizationCredentials => 'Temporary authorization credentials are invalid.',
            IdentityCodeExceptionEnum::TemporaryAuthorizationExpired => 'Temporary authorization has expired.',
            IdentityCodeExceptionEnum::TemporaryAuthorizationAttemptsExceeded => 'Temporary authorization credential attempts were exceeded.',
            IdentityCodeExceptionEnum::TemporaryAuthorizationCancelled => 'Temporary authorization was cancelled.',
            IdentityCodeExceptionEnum::TemporaryAuthorizationRevoked => 'Temporary authorization was revoked.',
            IdentityCodeExceptionEnum::TemporaryAuthorizationPendingCredentials => 'Temporary authorization is pending credentials.',
            IdentityCodeExceptionEnum::TemporaryAuthorizationFinished => 'Temporary authorization is already finished.',
            IdentityCodeExceptionEnum::TemporaryAuthorizationContextMismatch => 'Temporary authorization context does not match.',
            IdentityCodeExceptionEnum::TemporaryAuthorizationProtectedActionMismatch => 'Temporary authorization protected action does not match.',
            IdentityCodeExceptionEnum::InvalidTemporaryAuthorizationStatusTransition => 'Temporary authorization status transition is invalid.',
            default => 'Identity application error.',
        };
    }
}
