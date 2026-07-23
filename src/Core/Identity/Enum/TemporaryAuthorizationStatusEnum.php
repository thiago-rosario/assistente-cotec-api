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
}
