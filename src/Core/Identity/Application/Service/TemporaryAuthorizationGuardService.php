<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Service;

use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationErrorCodeServiceInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationGuardServiceInterface;
use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\Policy\TemporaryAuthorizationPolicy;
use App\Core\Identity\Domain\Repository\TemporaryAuthorizationRepositoryInterface;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use App\Core\Identity\Enum\ProtectedActionEnum;
use App\Core\Identity\Enum\TemporaryAuthorizationStatusEnum;
use App\Core\Identity\Exception\IdentityApplicationException;
use DateTimeImmutable;

class TemporaryAuthorizationGuardService implements TemporaryAuthorizationGuardServiceInterface
{
    public function __construct(
        private readonly TemporaryAuthorizationPolicy $policy,
        private readonly TemporaryAuthorizationRepositoryInterface $repository,
        private readonly TemporaryAuthorizationErrorCodeServiceInterface $errorCodeService,
    ) {}

    public function assertContextMatches(TemporaryAuthorizationEntity $authorization, AuthorizationContext $context): void
    {
        if (! $authorization->context->equals($context)) {
            throw new IdentityApplicationException(IdentityCodeExceptionEnum::TemporaryAuthorizationContextMismatch);
        }
    }

    public function assertProtectedActionMatches(TemporaryAuthorizationEntity $authorization, ProtectedActionEnum $protectedAction): void
    {
        if ($authorization->protectedAction !== $protectedAction) {
            throw new IdentityApplicationException(IdentityCodeExceptionEnum::TemporaryAuthorizationProtectedActionMismatch);
        }
    }

    public function assertNotExpired(TemporaryAuthorizationEntity $authorization, DateTimeImmutable $now): void
    {
        if ($authorization->status === TemporaryAuthorizationStatusEnum::Expired) {
            throw new IdentityApplicationException(IdentityCodeExceptionEnum::TemporaryAuthorizationExpired);
        }

        if ($this->policy->shouldExpire($authorization, $now)) {
            $this->repository->save($authorization->expire($now));

            throw new IdentityApplicationException(IdentityCodeExceptionEnum::TemporaryAuthorizationExpired);
        }
    }

    public function assertCancellable(TemporaryAuthorizationEntity $authorization): void
    {
        if ($authorization->status === TemporaryAuthorizationStatusEnum::Revoked
            || $authorization->status === TemporaryAuthorizationStatusEnum::AttemptsExceeded) {
            throw new IdentityApplicationException($this->errorCodeService->resolve($authorization));
        }
    }

    public function assertAuthorizedForExecution(TemporaryAuthorizationEntity $authorization, DateTimeImmutable $now): void
    {
        if (! $authorization->isAuthorized($now)) {
            throw new IdentityApplicationException($this->errorCodeService->resolveForExecution($authorization));
        }
    }

    public function assertCanAuthenticate(TemporaryAuthorizationEntity $authorization, AuthorizationContext $context, ProtectedActionEnum $action, DateTimeImmutable $now): void
    {
        $this->assertContextMatches($authorization, $context);
        $this->assertProtectedActionMatches($authorization, $action);
        $this->assertNotExpired($authorization, $now);

        if (! $this->policy->canAttempt($authorization, $now)) {
            throw new IdentityApplicationException($this->errorCodeService->resolve($authorization));
        }
    }

    public function assertAuthorized(TemporaryAuthorizationEntity $authorization, DateTimeImmutable $now): void
    {
        if ($authorization->status === TemporaryAuthorizationStatusEnum::Expired) {
            $this->repository->save($authorization);

            throw new IdentityApplicationException(IdentityCodeExceptionEnum::TemporaryAuthorizationExpired);
        }

        if (! $authorization->isAuthorized($now)) {
            throw new IdentityApplicationException(IdentityCodeExceptionEnum::InvalidTemporaryAuthorizationStatusTransition);
        }
    }
}
