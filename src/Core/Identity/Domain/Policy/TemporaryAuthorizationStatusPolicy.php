<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Policy;

use App\Core\Identity\Enum\TemporaryAuthorizationStatusEnum;

final class TemporaryAuthorizationStatusPolicy
{
    public static function isPending(TemporaryAuthorizationStatusEnum $status): bool
    {
        return $status === TemporaryAuthorizationStatusEnum::PendingCredentials;
    }

    public static function isAuthorized(TemporaryAuthorizationStatusEnum $status): bool
    {
        return $status === TemporaryAuthorizationStatusEnum::Authorized;
    }

    public static function isTerminal(TemporaryAuthorizationStatusEnum $status): bool
    {
        return match ($status) {
            TemporaryAuthorizationStatusEnum::AttemptsExceeded,
            TemporaryAuthorizationStatusEnum::Expired,
            TemporaryAuthorizationStatusEnum::Cancelled,
            TemporaryAuthorizationStatusEnum::Revoked => true,
            TemporaryAuthorizationStatusEnum::PendingCredentials,
            TemporaryAuthorizationStatusEnum::Authorized => false,
        };
    }
}
