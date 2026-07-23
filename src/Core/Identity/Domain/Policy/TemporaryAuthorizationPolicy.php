<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Policy;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;
use DateTimeInterface;

final class TemporaryAuthorizationPolicy
{
    public function canAttempt(
        TemporaryAuthorizationEntity $authorization,
        DateTimeInterface|string|null $now = null,
    ): bool {
        return $authorization->canReceiveCredentialAttempt($now);
    }

    public function canExecuteProtectedAction(
        TemporaryAuthorizationEntity $authorization,
        ProtectedActionEnum $protectedAction,
        AuthorizationContext $context,
        DateTimeInterface|string|null $now = null,
    ): bool {
        return $authorization->allows(
            protectedAction: $protectedAction,
            context: $context,
            now: $now,
        );
    }

    public function shouldExpire(
        TemporaryAuthorizationEntity $authorization,
        DateTimeInterface|string|null $now = null,
    ): bool {
        return ! TemporaryAuthorizationStatusPolicy::isTerminal($authorization->status)
            && $authorization->hasExpired($now);
    }
}
