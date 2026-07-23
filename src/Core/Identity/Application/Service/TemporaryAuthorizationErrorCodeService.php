<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Service;

use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationErrorCodeServiceInterface;
use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use App\Core\Identity\Enum\TemporaryAuthorizationStatusEnum;

class TemporaryAuthorizationErrorCodeService implements TemporaryAuthorizationErrorCodeServiceInterface
{
    public function resolve(TemporaryAuthorizationEntity $authorization): IdentityCodeExceptionEnum
    {
        return match ($authorization->status) {
            TemporaryAuthorizationStatusEnum::AttemptsExceeded => IdentityCodeExceptionEnum::TemporaryAuthorizationAttemptsExceeded,

            TemporaryAuthorizationStatusEnum::Expired => IdentityCodeExceptionEnum::TemporaryAuthorizationExpired,

            TemporaryAuthorizationStatusEnum::Cancelled => IdentityCodeExceptionEnum::TemporaryAuthorizationCancelled,

            TemporaryAuthorizationStatusEnum::Revoked => IdentityCodeExceptionEnum::TemporaryAuthorizationRevoked,

            TemporaryAuthorizationStatusEnum::PendingCredentials,
            TemporaryAuthorizationStatusEnum::Authorized => IdentityCodeExceptionEnum::InvalidTemporaryAuthorizationStatusTransition,
        };
    }

    public function resolveForExecution(TemporaryAuthorizationEntity $authorization): IdentityCodeExceptionEnum
    {
        return match ($authorization->status) {
            TemporaryAuthorizationStatusEnum::PendingCredentials => IdentityCodeExceptionEnum::TemporaryAuthorizationPendingCredentials,

            TemporaryAuthorizationStatusEnum::Authorized => IdentityCodeExceptionEnum::InvalidTemporaryAuthorizationStatusTransition,

            TemporaryAuthorizationStatusEnum::AttemptsExceeded => IdentityCodeExceptionEnum::TemporaryAuthorizationAttemptsExceeded,

            TemporaryAuthorizationStatusEnum::Expired => IdentityCodeExceptionEnum::TemporaryAuthorizationExpired,

            TemporaryAuthorizationStatusEnum::Cancelled => IdentityCodeExceptionEnum::TemporaryAuthorizationCancelled,

            TemporaryAuthorizationStatusEnum::Revoked => IdentityCodeExceptionEnum::TemporaryAuthorizationRevoked,
        };
    }
}
