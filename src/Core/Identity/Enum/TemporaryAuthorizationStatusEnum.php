<?php

declare(strict_types=1);

namespace App\Core\Identity\Enum;

enum TemporaryAuthorizationStatusEnum: string
{
    case PendingCredentials = 'pending_credentials';
    case Authorized = 'authorized';
    case AttemptsExceeded = 'attempts_exceeded';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Revoked = 'revoked';

    public function isPending(): bool
    {
        return $this === self::PendingCredentials;
    }

    public function isAuthorized(): bool
    {
        return $this === self::Authorized;
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::AttemptsExceeded,
            self::Expired,
            self::Cancelled,
            self::Revoked => true,
            self::PendingCredentials,
            self::Authorized => false,
        };
    }
}
