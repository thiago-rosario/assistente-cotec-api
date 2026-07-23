<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Repositories;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\Policy\TemporaryAuthorizationStatusPolicy;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;
use App\Core\Identity\Infra\Repository\Cache\CacheTemporaryAuthorizationRepository;
use DateTimeInterface;

final readonly class FindActiveTemporaryAuthorizationByContextCacheRepository
{
    public function __construct(
        private CacheTemporaryAuthorizationRepository $cacheRepository,
        private FindTemporaryAuthorizationByIdCacheRepository $findByIdRepository,
    ) {}

    public function findActiveByContext(
        AuthorizationContext $context,
        ProtectedActionEnum $protectedAction,
        DateTimeInterface|string|null $now = null,
    ): ?TemporaryAuthorizationEntity {
        $authorizationId = $this->cacheRepository->getContextAuthorizationId($context, $protectedAction);

        if ($authorizationId === null) {
            return null;
        }

        $authorization = $this->findByIdRepository->findById($authorizationId);

        if ($authorization === null
            || ! $authorization->context->equals($context)
            || $authorization->protectedAction !== $protectedAction
            || TemporaryAuthorizationStatusPolicy::isTerminal($authorization->status)) {
            $this->cacheRepository->forgetContextIndex($context, $protectedAction);

            return null;
        }

        return $authorization;
    }
}
